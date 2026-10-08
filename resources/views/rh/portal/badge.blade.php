<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Badge Collaborateur — {{ $employee->full_name }} — IVOSPHERE ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .badge-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 sm:p-8 font-sans">

    <!-- Barre d'outils (Non imprimée) -->
    <div class="no-print max-w-sm w-full mb-6 flex items-center justify-between">
        <a href="{{ route('rh.portal.index') }}" class="text-xs font-semibold text-slate-600 hover:text-[#0066FF] flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Retour Espace Collaborateur</span>
        </a>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Imprimer le Badge</span>
        </button>
    </div>

    <!-- CARTE BADGE PROFESSIONNELLE (Format standard CR80 / PVC) -->
    <div class="badge-card max-w-[340px] w-full bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col items-center text-center">
        
        <!-- En-tête Badge avec Dégradé IVOSPHERE -->
        <div class="w-full bg-gradient-to-r from-[#0B0F14] via-[#1E293B] to-[#0066FF] px-6 pt-6 pb-12 text-white relative">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="w-7 h-7 rounded-lg bg-white p-0.5 object-contain shadow-md">
                    <span class="font-extrabold text-sm tracking-wider">IVOSPHERE</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-white/20 text-slate-100 uppercase tracking-widest border border-white/20">
                    Badge Officiel
                </span>
            </div>
            <p class="text-[10px] text-slate-300 font-medium tracking-wide uppercase">Système de Présence & Contrôle d'Accès</p>
        </div>

        <!-- Avatar / Photo Collaborateur en superposition -->
        <div class="-mt-10 relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-white p-1 shadow-xl">
                <div class="w-full h-full rounded-xl bg-gradient-to-br from-[#0066FF] to-blue-800 text-white flex items-center justify-center font-extrabold text-2xl shadow-inner">
                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                </div>
            </div>
        </div>

        <!-- Informations Collaborateur -->
        <div class="px-6 pt-3 pb-2 w-full">
            <h2 class="text-lg font-black text-[#0B0F14] tracking-tight leading-tight">{{ $employee->full_name }}</h2>
            <p class="text-xs font-bold text-[#0066FF] mt-0.5">{{ $employee->position ?? 'Collaborateur' }}</p>
            <p class="text-[11px] text-slate-500 font-medium">{{ $employee->department ?? 'Département Opérations' }}</p>

            <!-- Matricule Officiel -->
            <div class="mt-3 py-1.5 px-3 rounded-xl bg-slate-50 border border-slate-200 inline-flex items-center gap-2 font-mono">
                <span class="text-[10px] text-slate-400 font-bold uppercase">Matricule :</span>
                <span class="text-xs font-black text-slate-900 tracking-wider">{{ $employee->employee_code ?? 'EMP-'.str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <!-- Zone QR Code Personnel de Pointage -->
        <div class="p-4 my-2 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col items-center">
            <div id="badgeQrcode" class="flex items-center justify-center p-2 bg-white rounded-xl shadow-sm border border-slate-100 min-h-[160px] min-w-[160px]"></div>
            
            <div class="mt-2.5 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-bold text-slate-700">Pointage disponible</span>
            </div>
        </div>

        <!-- Pied de badge : Conditions & Sécurité -->
        <div class="w-full bg-slate-50 border-t border-slate-100 px-6 py-3.5 text-center text-[10px] text-slate-500 space-y-0.5">
            <p class="font-semibold text-slate-700">Propriété exclusive d'IVOSPHERE</p>
            <p class="text-[9px] text-slate-400 leading-tight">En cas de perte, merci de contacter la Direction RH.</p>
        </div>
    </div>

    <!-- Script de rendu du QR Code -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const qrPayload = JSON.stringify({
                type: 'employee_badge',
                employee_id: {{ $employee->id }},
                matricule: "{{ $employee->employee_code ?? 'EMP-'.$employee->id }}",
                token: "{{ hash_hmac('sha256', $employee->id.'-'.$employee->employee_code, config('app.key')) }}"
            });

            new QRCode(document.getElementById("badgeQrcode"), {
                text: qrPayload,
                width: 150,
                height: 150,
                colorDark : "#0B0F14",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        });
    </script>
</body>
</html>
