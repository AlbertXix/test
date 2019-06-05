<?php
echo get_magic_quotes_gpc();
echo "<br />";
set_magic_quotes_runtime(1);
$str = "I'll come back later.";
$str2 = "<strong>Hey, A&B</strong>";
echo addslashes($str);
echo addslashes($str2)."<br />";
echo htmlspecialchars($str2);

$htmm = <<<EOT
<h1>Hello, world!</h1>
<p>Good morning, sir.</p>
EOT;
echo $htmm;
?>