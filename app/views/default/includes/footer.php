</div>
<footer class="footer mt-auto py-3 text-center">
    <!-- Localization button -->
    <div class="dropdown mt-2">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            {@localization[lang]}}
        </button>
        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=en" rel="nofollow">English</a></li>
            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=sr" rel="nofollow">Српски</a></li>
        </ul>
    </div>
    <div class="mt-3">
        {@show_online}}{@show_counter}}{@show_generation_time}}{@show_debug}}
        <span>Powered by <a href="https://vavok.net/">vavok.net</a></span>
    </div>
</footer><!-- end of footer -->
<script src="{@HOMEDIR}}themes/default/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>