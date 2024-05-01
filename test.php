<?php

require_once 'init.php';

require_once 'header.php';

?>
<div class="tw-w-[25rem] tw-h-[25rem] p-5">
    <?= updatableImage('https://picsum.photos/id/237/200/300', 'updateImage()'); ?>
</div>

<script>
    function updateImage() {
        const newImageUrl = prompt('Enter new image URL');
        console.log(newImageUrl);
    }
</script>

<?php
require_once 'footer.php';
