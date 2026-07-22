<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:24px;">
        <div style="text-align:center;margin-bottom:24px;">
            <span style="display:inline-block;font-size:20px;font-weight:bold;color:#0f172a;">
                IA<span style="color:#0EA5A0;">DIAL</span>
            </span>
        </div>

        <h2 style="margin-top:0;color:#0f172a;">Bienvenue, {{ $user->name }} 👋</h2>

        <p style="color:#334155;line-height:1.6;">
            Votre compte IA DIAL vient d'être créé pour l'offre
            <strong>{{ $user->plan_label ?? 'IA DIAL' }}</strong>.
            Voici vos identifiants de connexion :
        </p>

        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr>
                <td style="padding:10px;border-bottom:1px solid #eee;font-weight:bold;width:160px;vertical-align:top;">
                    E-mail
                </td>
                <td style="padding:10px;border-bottom:1px solid #eee;">
                    {{ $user->email }}
                </td>
            </tr>
            <tr>
                <td style="padding:10px;border-bottom:1px solid #eee;font-weight:bold;vertical-align:top;">
                    Mot de passe temporaire
                </td>
                <td style="padding:10px;border-bottom:1px solid #eee;">
                    <span style="font-family:'Courier New',monospace;background:#f1f5f9;padding:4px 8px;border-radius:4px;display:inline-block;">
                        {{ $temporaryPassword }}
                    </span>
                </td>
            </tr>
        </table>

        <p style="color:#334155;line-height:1.6;">
            Votre offre sera activée automatiquement dès la confirmation de votre paiement.
            Vous pouvez dès maintenant accéder à votre espace pour suivre l'état de votre compte.
        </p>

        <div style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:6px;padding:14px 16px;margin:20px 0;">
            <p style="color:#065F46;font-size:14px;line-height:1.6;margin:0;">
                🕒 <strong>Votre réceptionniste IA sera activé sous 24h</strong> après confirmation de votre paiement. Vous recevrez une notification dès qu'il sera opérationnel et prêt à répondre à vos clients.
            </p>
        </div>

        <div style="text-align:center;margin:28px 0;">
            <a href="{{ $loginUrl }}"
               style="display:inline-block;background:#0EA5A0;color:#ffffff;text-decoration:none;
                      padding:12px 24px;border-radius:6px;font-weight:bold;">
                Accéder à mon espace
            </a>
        </div>

        <p style="color:#94a3b8;font-size:12px;line-height:1.6;">
            Pour votre sécurité, nous vous recommandons de modifier ce mot de passe dès votre première connexion.
            Si vous n'êtes pas à l'origine de cette inscription, contactez-nous à
            <a href="mailto:contact@iadial.com" style="color:#0EA5A0;">contact@iadial.com</a>.
        </p>
    </div>
</body>
</html>