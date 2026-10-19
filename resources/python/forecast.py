"""Prévisions IVOSPHERE : ventes des prochains jours et ruptures de stock.

Lit sur l'entrée standard un JSON préparé par Laravel (BusinessForecastService) et écrit
le résultat en JSON sur la sortie standard. Utilise scikit-learn s'il est installé,
sinon une régression linéaire numpy équivalente.
"""

import json
import math
import sys
from datetime import date, timedelta

import numpy as np
import pandas as pd

try:
    from sklearn.linear_model import Ridge
except ImportError:  # pragma: no cover - dépend du serveur
    Ridge = None

MIN_HISTORY_DAYS = 28


def daily_series(rows, today, history_days, value_key):
    """Série journalière continue (jours sans vente = 0) sur la période d'historique."""
    start = today - timedelta(days=history_days - 1)
    index = pd.date_range(start, today, freq="D")
    if not rows:
        return pd.Series(0.0, index=index)
    frame = pd.DataFrame(rows)
    frame["date"] = pd.to_datetime(frame["date"])
    series = frame.groupby("date")[value_key].sum().astype(float)
    return series.reindex(index, fill_value=0.0)


def features(dates, origin):
    """Tendance (jours écoulés) + jour de la semaine (one-hot)."""
    trend = np.array([(d - origin).days for d in dates], dtype=float).reshape(-1, 1)
    weekdays = np.zeros((len(dates), 7))
    for row, d in enumerate(dates):
        weekdays[row, d.weekday()] = 1.0
    return np.hstack([trend, weekdays])


def fit_predict(x_train, y_train, x_future):
    if Ridge is not None:
        model = Ridge(alpha=1.0)
        model.fit(x_train, y_train)
        return model.predict(x_future)
    design = np.hstack([x_train, np.ones((len(x_train), 1))])
    coefficients, *_ = np.linalg.lstsq(design, y_train, rcond=None)
    return np.hstack([x_future, np.ones((len(x_future), 1))]) @ coefficients


def forecast_sales(payload, today):
    horizon = int(payload.get("horizon", 30))
    history = daily_series(payload.get("sales", []), today, int(payload.get("history_days", 180)), "amount")
    active = history[history.ne(0).cumsum() > 0]
    future_dates = [today + timedelta(days=i) for i in range(1, horizon + 1)]

    if len(active) >= MIN_HISTORY_DAYS:
        origin = active.index[0].date()
        x_train = features([d.date() for d in active.index], origin)
        predicted = fit_predict(x_train, active.values, features(future_dates, origin))
        # Garde-fou : la projection ne s'éloigne pas de plus de x3 du rythme récent
        recent_mean = float(active.tail(28).mean())
        predicted = np.clip(predicted, 0, max(recent_mean * 3, 1))
        method = "regression"
    else:
        recent_mean = float(active.mean()) if len(active) else 0.0
        predicted = np.full(horizon, recent_mean)
        method = "moyenne"

    last_30 = float(history.tail(30).sum())
    next_30 = float(predicted[:30].sum())
    trend = round((next_30 - last_30) / last_30 * 100, 1) if last_30 > 0 else None

    by_domain = []
    domain_rows = [row for row in payload.get("sales", []) if row.get("domain")]
    if domain_rows and next_30 > 0:
        frame = pd.DataFrame(domain_rows)
        frame["date"] = pd.to_datetime(frame["date"])
        recent = frame[frame["date"] >= pd.Timestamp(today - timedelta(days=89))]
        shares = recent.groupby("domain")["amount"].sum()
        if shares.sum() > 0:
            shares = shares / shares.sum()
            by_domain = [
                {"domain": domain, "next_30_days": round(next_30 * share)}
                for domain, share in shares.sort_values(ascending=False).items()
            ]

    return {
        "method": method,
        "history_days": int(len(active)),
        "next_7_days": round(float(predicted[:7].sum())),
        "next_30_days": round(next_30),
        "last_30_days": round(last_30),
        "trend_pct": trend,
        "daily": [
            {"date": d.isoformat(), "amount": round(float(v))}
            for d, v in zip(future_dates[:14], predicted[:14])
        ],
        "by_domain": by_domain,
    }


def forecast_stockouts(payload, today):
    results = []
    for product in payload.get("products", []):
        series = daily_series(product.get("sales", []), today, 60, "qty")
        rate = 0.6 * float(series.tail(14).mean()) + 0.4 * float(series.mean())
        if rate <= 0:
            continue
        stock = float(product.get("stock", 0))
        min_stock = float(product.get("min_stock", 0))
        days_left = max(stock, 0) / rate
        reorder = math.ceil(rate * 30 + min_stock - stock)
        results.append({
            "id": product["id"],
            "name": product["name"],
            "stock": stock,
            "daily_rate": round(rate, 2),
            "days_left": round(days_left, 1),
            "stockout_date": (today + timedelta(days=math.floor(days_left))).isoformat(),
            "reorder_qty": max(reorder, 0),
        })
    results.sort(key=lambda item: item["days_left"])
    return results


def main():
    payload = json.load(sys.stdin)
    today = date.fromisoformat(payload["today"])
    json.dump({
        "engine": "python",
        "sales": forecast_sales(payload, today),
        "stockouts": forecast_stockouts(payload, today),
    }, sys.stdout)


if __name__ == "__main__":
    main()
