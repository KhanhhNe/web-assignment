<?php

require_once 'init.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['autocomplete'])) {
    $q = $_GET['autocomplete'];
    $query = $db->query("SELECT * FROM products WHERE name LIKE '%$q%' LIMIT 5");
    $data = [];
    while ($product = $query->fetch_assoc()) {
        $data[] = $product;
    }
    echo json_encode($data);
    die();
}

if ($_POST['action'] ?? '' == 'delete') {
    $id = $_POST['id'];
    $db->query("DELETE FROM products WHERE id = $id");
    $_SESSION['message'] = "Product $id deleted!";
    echo json_encode(['success' => true]);
    die();
}

if ($_POST['action'] ?? '' == 'update') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $db->query("UPDATE products SET name = '$name' WHERE id = $id");
    $_SESSION['message'] = "Product $id updated!";
    echo json_encode(['success' => true]);
    die();
}

if ($_POST['action'] ?? '' == 'add') {
    $name = $_POST['name'];
    $db->query("INSERT INTO products (name) VALUES ('$name')");
    $_SESSION['message'] = "Product `$name` added!";
    header('Location: /products.php');
    die();
}

$q = $_GET['q'] ?? '';
$limit = $_GET['limit'] ?? 10;
$page = $_GET['page'] ?? 1;

$offset = ($page - 1) * $limit;
$pages = ceil($db->query("SELECT COUNT(*) FROM products")->fetch_assoc()['COUNT(*)'] / $limit);

function get_page_link($args)
{
    global $page, $limit, $q;

    $link_page = $args['page'] ?? $page;
    $link_limit = $args['limit'] ?? $limit;
    $link_q = $args['q'] ?? $q;
    return "/products.php?page=$link_page&limit=$link_limit&q=$link_q";
}

require_once 'header.php';
?>

<div class="container my-5 d-flex flex-column align-items-center gap-3">
    <h1 class="h1">Products</h1>

    <div class="d-flex flex-column align-items-center gap-1 my-5">
        <form action="/products.php" method="post" class="container p-0">
            <div class="d-flex gap-1">
                <input name="action" type="hidden" value="add" />
                <input name="name" type="text" class="form-control" placeholder="Name" style="max-width: 15rem;" />
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>

        <form action="/products.php" method="get" class="container p-0">
            <div class="d-flex gap-1">
                <div class="dropdown">
                    <input name="q" type="text" class="form-control" data-bs-toggle="dropdown" placeholder="Search..." style="max-width: 15rem;" />
                    <ul id="autocomplete" class="dropdown-menu p-2"></ul>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="/products.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="d-flex justify-content-between w-100 mb-1">
            <div class="btn-group">
                <a href="<?= get_page_link(['limit' => 10]) ?>" class="btn btn-secondary">10</a>
                <a href="<?= get_page_link(['limit' => 20]) ?>" class="btn btn-secondary">20</a>
                <a href="<?= get_page_link(['limit' => 50]) ?>" class="btn btn-secondary">50</a>
            </div>

            <div class="btn-group">
                <a href="<?= get_page_link(['page' => 1]) ?>" class="btn btn-secondary <?php if ($page == 1) echo 'disabled' ?>">First</a>
                <a href="<?= get_page_link(['page' => $page - 1]) ?>" class="btn btn-secondary <?php if ($page == 1) echo 'disabled' ?>">Prev</a>
                <a href="<?= get_page_link(['page' => $page + 1]) ?>" class="btn btn-secondary <?php if ($page >= $pages) echo 'disabled' ?>">Next</a>
                <a href="<?= get_page_link(['page' => $pages]) ?>" class="btn btn-secondary <?php if ($page >= $pages) echo 'disabled' ?>">Last</a>
            </div>
        </div>

        <table class="table table-striped" style="width: 40rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $products = $db->query("SELECT * FROM products WHERE name LIKE '%$q%' LIMIT $limit OFFSET $offset");
                while ($product = $products->fetch_assoc()) {
                ?>
                    <tr id="product-<?= $product['id'] ?>">
                        <td><?= $product['id'] ?></td>
                        <td class="w-100" contenteditable><?= $product['name'] ?></td>
                        <td class="text-nowrap">
                            <button type="submit" class="btn btn-success btn-sm" onclick="updateProduct(<?= $product['id'] ?>)">
                                Update
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteProduct(<?= $product['id'] ?>)">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (window.location.search.length > 0) {
            const q = new URLSearchParams(window.location.search).get('q');
            if (q) {
                $('input[name="q"]').val(q);
            }
        }
    });

    $('input[name="q"]').on('input', function() {
        const q = $(this).val();
        if (q.length > 0) {
            $.getJSON('/products.php?autocomplete=' + q, function(data) {
                $('#autocomplete').html('');
                data.forEach(function(item) {
                    $('#autocomplete').append(`
                        <li class="text-nowrap">
                            <a href="/products.php?q=${item['name']}" class="text-decoration-none text-muted">
                                ${item['name']}
                            </a>
                        </li>
                    `);
                });
            });
        } else {
            $('#autocomplete').html('');
        }
    });

    function deleteProduct(id) {
        if (confirm('Are you sure you want to delete this product?')) {
            $.post('/products.php', {
                id: id,
                action: 'delete'
            }, function(data) {
                if (JSON.parse(data).success) {
                    location.reload();
                } else {
                    alert('Failed to delete product');
                }
            });
        }
    }

    function updateProduct(id) {
        const name = document.getElementById('product-' + id).children[1].innerText;
        $.post('/products.php', {
            id: id,
            name: name,
            action: 'update'
        }, function(data) {
            if (JSON.parse(data).success) {
                location.reload();
            } else {
                alert('Failed to update product');
            }
        });
    }
</script>

<?php
require_once 'footer.php';
