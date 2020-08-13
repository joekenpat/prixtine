    <nav class='uk-navbar-container navigation' uk-navbar>
        <div class='uk-navbar-left '>
            <?php if (isset($_SESSION['email'])) : ?>
                <button class='uk-navbar-toggle ' style='color: white;' type='button' uk-toggle='target: #offcanvas-push' uk-navbar-toggle-icon href='#'></button>
            <?php endif; ?>
        </div>
        <div class='uk-navbar-left'>
            <ul class='uk-navbar-nav '>
                <?php if (!isset($_SESSION['email']) && ($_SERVER['PHP_SELF'] == '/login.php' || $_SERVER['PHP_SELF'] == '/registration.php')) : ?>
                    <li class='uk-active'><a href='//prixtine.com.ng' class='uk-button nav-btn'>
                            <b style="color: white"><i class="mdi mdi-home"></i> Home</b>
                        </a></li>";
                <?php endif; ?>
                <?php if ($role == 'admin') : ?>
                    <li class='uk-active'><b style='color:white'>Admin Dashboard</b><i class='mdi  mdi-circle-medium' style='color:#9efd38; font-size: 25px'></i></li>";
                <?php elseif ($role == 'user') : ?>
                    <li class='uk-active'><b style='color:white'>User Dashboard</b><i class='mdi  mdi-circle-medium' style='color:#9efd38; font-size: 25px'></i></li>";
                <?php endif; ?>
            </ul>
        </div>

        <div class='uk-navbar-right'>
            <ul class='uk-navbar-nav'>
                <?php if (isset($_SESSION['email'])) : ?>
                    <li><a href='./logout' class='uk-button nav-btn'>
                            <b style="color: white"><i class="mdi mdi-lock"></i> Logout</b>
                        </a></li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['email']) && $_SERVER['PHP_SELF'] != '/login.php') : ?>
                    <li><a href='./login' class='uk-button nav-btn'>
                            <b style="color: white"><i class="mdi mdi-lock-open"></i> Login</b>
                        </a></li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['email']) && $_SERVER['PHP_SELF'] != '/registration.php') : ?>
                    <li><a href='./registration' class='uk-button nav-btn'>
                            <b style="color: white"><i class="mdi mdi-account-plus"></i> Register</b>
                        </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>