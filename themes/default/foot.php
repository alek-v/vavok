</div><!-- end of div#container -->

<div class="c">
<?php
echo '<span><a href="' . $config["homeUrl"] . '/">' . $config["siteCopy"] . '</a><br /></span>';
echo '</div>
<footer>
<div class="centertext">';

if ($config["showOnline"] == 1) {
echo '<span>' . show_online() . '<br /></span>';
}
if ($config["showCounter"] != 6) {
	echo '<span>' . show_counter() . '<br /></span>';
}
if ($config["pageGenTime"] == 1) {
    echo '<span>' . show_gentime() . '<br /></span>';
} 

echo '<span>powered by <a href="' . $connectionProtocol . 'www.vavok.net/">Vavok.net</a></span>';
?>

</div><!-- end of div.centertext -->
</footer><!-- end of footer -->
</div><!-- end of div#wrapper -->
</body>
</html>