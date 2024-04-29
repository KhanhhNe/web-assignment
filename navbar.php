<nav class="navbar navbar-expand-sm bg-bk-dark" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/assets/logo.png" alt="Logo" width="50" height="50">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navbar" class="collapse navbar-collapse justify-content-between">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>

                <?php if (isset($_SESSION['user_id'])) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">Dashboard</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php">
                            <button class="btn btn-primary">Logout</button>
                        </a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">
                            <button class="btn btn-primary">Login</button>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>