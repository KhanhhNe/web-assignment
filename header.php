<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My website</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            prefix: 'tw-'
        }

        $(document).on('ajaxComplete', function(event, xhr, settings) {
            if (xhr.responseJSON && !xhr.responseJSON.success) {
                showToast(xhr.responseJSON.error, {
                    type: 'danger'
                });
            }
        });

        $(document).ready(function() {
            if (window.location.search.includes('debug')) {
                $('body').addClass('debug');
            }
        });

        $.ajaxSetup({
            dataType: 'json',
        });
    </script>

    <link rel="stylesheet" href="/index.css">
</head>

<body>
    <?php require_once 'navbar.php'; ?>

    <?php if (!empty($_SESSION['message'])) { ?>
        <div class="alert alert-primary mx-5 mt-5" role="alert">
            <?php echo $_SESSION['message']; ?>
        </div>
    <?php
        unset($_SESSION['message']);
    } ?>