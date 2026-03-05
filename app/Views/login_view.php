<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | SweetyDog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/style.css')) ?>">
    <style>
        :root {
            --vert-fonce: #1b4332;
            --vert-moyen: #2d6a4f;
            --blanc-casse: #f8f9fa;
        }

        body {
            margin: 0;
            padding: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--vert-fonce) 0%, var(--vert-moyen) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        .logo-area {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        h1 {
            color: var(--vert-fonce);
            margin: 0 0 5px 0;
            font-size: 1.8rem;
            letter-spacing: -1px;
        }

        p.subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #edf2f7;
            border-radius: 10px;
            box-sizing: border-box;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--vert-moyen);
            background-color: #f0fff4;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--vert-fonce);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: var(--vert-moyen);
            transform: translateY(-2px);
        }

        .error-banner {
            background-color: #fff5f5;
            color: #c53030;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #feb2b2;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 0.75rem;
            color: #bdc3c7;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
                align-items: stretch;
            }

            .login-container {
                max-width: 100%;
                margin: auto 0;
                padding: 24px 16px;
                border-radius: 14px;
            }

            .logo-area { font-size: 2.4rem; }
            h1 { font-size: 1.45rem; }
            p.subtitle { margin-bottom: 18px; font-size: 0.85rem; }
            .form-group { margin-bottom: 14px; }
            .btn-submit { padding: 12px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-area">🦴</div>
    <h1>SweetyDog</h1>
    <p class="subtitle">Espace d'administration du salon</p>

    <?php if (isset($erreur) && $erreur): ?>
        <div class="error-banner">
            ⚠️ <?php echo $erreur; ?>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars(route('login')) ?>" method="POST">
        <?= csrf_field(); ?>
        <div class="form-group">
            <label for="identifiant">Identifiant</label>
            <input type="text" id="identifiant" name="identifiant" placeholder="Ex: admin" required autofocus>
        </div>

        <div class="form-group">
            <label for="mdp">Mot de passe</label>
            <input type="password" id="mdp" name="mdp" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">S'identifier</button>
    </form>

    <div class="footer-text" style="text-align:left;line-height:1.5;">
        <strong>Mode démo</strong> : identifiant <code>admin</code> / mot de passe <code>admin1</code><br>
        Voir les nouveautés : <a href="/nouveautes.php">/nouveautes.php</a><br><br>
        &copy; 2026 SweetyDog Management System
    </div>
</div>

</body>
</html>
