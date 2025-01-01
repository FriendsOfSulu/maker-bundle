<?php
/**
 * @var string $resource_key
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Preview of <?= $resource_key; ?></title>
    </head>
    <body>
        {% block content %}
            <!-- Put your rendering code here -->
            {{ dump() }}
        {% endblock %}
    </body>
</html>
