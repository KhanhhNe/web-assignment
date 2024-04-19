<?php

require_once 'init.php';

require_once 'header.php';

$total_products = $db->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
?>

<div class="container py-5">
    <h1 class="h1">Products</h1>
    <span>Total products: <?php echo $total_products; ?>. <a href="/products.php">View all products</a></span>

    <h1 class="h1 mt-5">Maps</h1>
    <div class="row row-cols-12">
        <div id="maps" class="col"></div>
        <div id="embed-map" class="col"></div>
    </div>
</div>

<script>
    let stores;

    $.ajax('/stores.json').done(function(data) {
        stores = data;
        console.log(stores);

        for (const [index, store] of Object.entries(stores)) {
            $('#maps').append(`
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">${store.city}, ${store.state}, ${store.country}</h5>
                        <p class="card-text"><a href="${store.mapUrl}" target="_blank">${store.mapUrl}</a></p>
                        <button onclick="showMap(${index})" class="btn btn-primary">View</button>
                    </div>
                </div>
            `);
        }

        showMap(0);
    })

    function showMap(index) {
        const store = stores[index];
        $('#embed-map').html(store.iframe);
    }
</script>

<?php
require_once 'footer.php';
