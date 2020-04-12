</div>

<footer class="footer mt-auto py-3 text-center">

<?php

if ($config["showOnline"] == 1) {
echo '<span>' . show_online() . '<br /></span>';
}
if ($config["showCounter"] != 6) {
	echo '<span>' . show_counter() . '<br /></span>';
}
if ($config["pageGenTime"] == 1) {
    echo '<span>' . show_gentime() . '<br /></span>';
} 

echo '<span>powered by <a href="https://www.vavok.net/" class="sitelink">Vavok.net</a></span>';
?>


</footer><!-- end of footer -->

</body>
</html>