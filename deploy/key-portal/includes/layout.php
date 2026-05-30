<?php

declare(strict_types=1);

/**
 * @param  list<array{href: string, label: string}>  $nav
 */
function keyportal_layout_start(string $title, array $nav = []): void
{
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> – key.luetcke.eu</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --text: #e2e8f0; --muted: #94a3b8; --accent: #6366f1; --danger: #f87171; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: var(--bg); color: var(--text); min-height: 100vh; }
        header { background: var(--card); border-bottom: 1px solid #334155; padding: 1rem 1.5rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; justify-content: space-between; }
        header nav { display: flex; gap: 1rem; flex-wrap: wrap; }
        header a { color: var(--muted); text-decoration: none; }
        header a:hover { color: var(--accent); }
        main { max-width: 56rem; margin: 0 auto; padding: 1.5rem; }
        .card { background: var(--card); border: 1px solid #334155; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; margin: 0 0 1rem; }
        label { display: block; font-size: 0.875rem; margin: 0.75rem 0 0.25rem; color: var(--muted); }
        input[type="text"], input[type="password"], textarea {
            width: 100%; max-width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid #475569;
            background: #0f172a; color: var(--text); font-size: 1rem;
        }
        textarea { min-height: 8rem; font-family: ui-monospace, monospace; }
        button, .btn {
            display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; border: none; background: var(--accent);
            color: #fff; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.875rem;
        }
        button.danger, .btn.danger { background: #991b1b; }
        button.secondary { background: #475569; }
        .error { color: var(--danger); margin: 0.5rem 0; font-size: 0.875rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th, td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #334155; vertical-align: top; }
        th { color: var(--muted); font-weight: 600; }
        code { background: #0f172a; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-size: 0.8125rem; word-break: break-all; }
        .row-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>
<header>
    <strong>Key-Portal</strong>
    <nav>
        <?php foreach ($nav as $item): ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </nav>
</header>
<main>
<?php
}

function keyportal_layout_end(): void
{
    ?>
</main>
</body>
</html>
<?php
}
