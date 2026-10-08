<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche de Tâches du Jour — IVOSPHERE</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F6F9FC; margin: 0; padding: 30px 15px; color: #0B0F14;">

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
        <!-- En-tête -->
        <tr>
            <td style="background-color: #0066FF; padding: 25px 30px; text-align: left;">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                                IVOSPHERE <span style="font-size: 14px; font-weight: normal; opacity: 0.85;">• ERP RH</span>
                            </div>
                            <div style="font-size: 12px; color: #ffffff; opacity: 0.9; margin-top: 4px;">
                                Fiche d'Attribution des Tâches du Jour
                            </div>
                        </td>
                        <td align="right">
                            <span style="display: inline-block; padding: 4px 10px; background-color: rgba(255,255,255,0.2); border-radius: 20px; font-size: 11px; font-weight: bold; color: #ffffff;">
                                {{ $sheet->date->format('d/m/Y') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Corps du message -->
        <tr>
            <td style="padding: 30px;">
                <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 10px 0; color: #0B0F14;">
                    Bonjour {{ $employee->first_name }},
                </h2>
                <p style="font-size: 13px; color: #64748B; line-height: 1.5; margin: 0 0 20px 0;">
                    Voici vos objectifs et la liste des tâches quotidiennes qui vous ont été assignées pour aujourd'hui.
                </p>

                <!-- Récapitulatif Employé -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; margin-bottom: 25px;">
                    <tr>
                        <td style="padding: 15px;">
                            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                                <tr>
                                    <td width="30%" style="color: #64748B; font-weight: 600;">Collaborateur :</td>
                                    <td style="color: #0B0F14; font-weight: bold;">{{ $employee->full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; font-weight: 600;">Poste / Fonction :</td>
                                    <td style="color: #0066FF; font-weight: bold;">{{ $sheet->position }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; font-weight: 600;">Référence :</td>
                                    <td style="color: #0B0F14; font-family: monospace;">{{ $sheet->reference }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Liste des Tâches -->
                <h3 style="font-size: 14px; font-weight: 700; color: #0B0F14; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                    Vos Tâches du Jour :
                </h3>

                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                    @if(is_array($sheet->tasks))
                        @foreach($sheet->tasks as $idx => $task)
                            @php
                                $title = is_array($task) ? ($task['title'] ?? '') : (string) $task;
                            @endphp
                            <tr>
                                <td style="padding: 10px 12px; border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="28" valign="top">
                                                <div style="width: 20px; height: 20px; border-radius: 6px; background-color: #EBF3FF; color: #0066FF; font-size: 11px; font-weight: bold; text-align: center; line-height: 20px;">
                                                    {{ $idx + 1 }}
                                                </div>
                                            </td>
                                            <td style="color: #1E293B; line-height: 1.4;">
                                                {{ $title }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </table>

                @if($sheet->notes)
                    <!-- Notes / Consignes -->
                    <div style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; padding: 12px 15px; border-radius: 0 8px 8px 0; margin-bottom: 25px; font-size: 12px; color: #92400E;">
                        <strong>Consignes particulières :</strong><br>
                        {{ $sheet->notes }}
                    </div>
                @endif

                <!-- Bouton d'action -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
                    <tr>
                        <td align="center">
                            <a href="{{ route('rh.daily-tasks.print', $sheet) }}" target="_blank" style="display: inline-block; background-color: #0066FF; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; padding: 12px 25px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,102,255,0.25);">
                                📄 Télécharger / Imprimer la Fiche PDF
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Pied de page -->
        <tr>
            <td style="background-color: #F8FAFC; padding: 20px 30px; text-align: center; border-top: 1px solid #E2E8F0; font-size: 11px; color: #94A3B8;">
                Ce message a été généré automatiquement par le module RH d'IVOSPHERE CRM.<br>
                Pour toute question, contactez votre responsable d'équipe ou les Ressources Humaines.
            </td>
        </tr>
    </table>

</body>
</html>
