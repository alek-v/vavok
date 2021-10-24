</div>

<footer class="footer mt-auto py-3 text-center">

<?php

if ($vavok->get_configuration('showOnline') == 1) {
echo '<span>' . $vavok->show_online() . '<br /></span>';
}
if ($vavok->get_configuration('showCounter') != 6) {
	echo '<span>' . $vavok->show_counter() . '<br /></span>';
}
if ($vavok->get_configuration('pageGenTime') == 1) {
    echo '<span>' . $vavok->show_gentime() . '<br /></span>';
}

// Show database queries while debugging
if (defined('SITE_STAGE') && SITE_STAGE == 'debug') {
    echo '<span>DB queries: ' . $vavok->go('db')->show_db_queries() . '<br /></span>';
}

echo '<span>powered by <a href="https://www.vavok.net/" class="sitelink">Vavok.net</a></span>';
?>


</footer><!-- end of footer -->

</body>
</html>