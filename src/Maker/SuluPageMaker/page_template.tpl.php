<?php
/**
 * @var string $configPath
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{ content.title }}</title>
</head>
<body>
    <h1>{{ content.title }}</h1>
<p>
The configuration for this page is under: <pre><?= $configPath; ?></pre>.
<br />
Here are some more properties of this page:
{{ dump(content) }}
</p>
</body>
</html>
