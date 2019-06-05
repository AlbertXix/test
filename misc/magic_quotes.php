<?php
echo "<ul style='list-style:circle'>";
echo "<li>addslashes function example(befor): I'm a addslashes...</li>";
echo "<li><strong>".addslashes("after addslashes: I'm a addslashes...")."</strong></li>";
echo "<li>stripslashes function example(befor): I\'m a stripslashes...</li>";
echo "<li><strong>".stripslashes("after stripslashes: I\'m a stripslashes...")."</strong></li>";
echo "<li>magic_quotes_gpc setting is: ".get_magic_quotes_gpc()."</li>";
echo "</ul>";
?>