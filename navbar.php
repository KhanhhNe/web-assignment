<nav class="navbar navbar-expand-sm navbar-light bg-light px-5">
    <a class="navbar-brand" href="/">My website</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/products.php">Products</a>
            </li>

            <?php if (isset($_SESSION['username'])) : ?>
                <li class="nav-item">
                    <a class="nav-link" href="/logout.php">Logout</a>
                </li>
            <?php else : ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>