<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$db = mysqli_connect('localhost', 'root', 'root', 'lab32');
if (!$db) {
  die('Could not connect db');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MAMP</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reset-css@5.0.2/reset.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>

<body class="container p-5">
  <?php if (!empty($_SESSION['message'])) { ?>
    <div class="alert alert-primary" role="alert">
      <?php echo $_SESSION['message']; ?>
    </div>
  <?php
    unset($_SESSION['message']);
  } ?>

  <h1 class="h1">Country - State - City Database</h1>

  <form>
    <div class="d-flex flex-column" style="gap: 1rem; max-width: 20rem;">
      <div>
        <label for="countries" class="form-label">Country</label>
        <select id="countries" class="form-select">
          <option selected>--Country--</option>
        </select>
      </div>

      <div>
        <label for="staties" class="form-label">States</label>
        <select id="states" class="form-select">
          <option selected>--State--</option>
        </select>
      </div>

      <div>
        <label for="cities" class="form-label">Cities</label>
        <select id="cities" class="form-select">
          <option selected>--City--</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </form>

  <script>
    let cities = {};
    $.get('/cities.json', function(data) {
      cities = data;

      $('#countries option[value]').remove();

      const countries = Object.keys(cities);
      countries.forEach(country => {
        $('#countries').append(`<option value="${country}">${country}</option>`);
      });
    });

    $('#countries').change(function() {
      const country = $(this).val();
      $('#states option[value]').remove();

      if (country === '--Country--') {
        return;
      }

      const states = Object.keys(cities[country]);
      states.forEach(state => {
        $('#states').append(`<option value="${state}">${state}</option>`);
      });
    });

    $('#states').change(function() {
      const country = $('#countries').val();
      const state = $(this).val();
      $('#cities option[value]').remove();

      if (state === '--State--') {
        return;
      }

      const citiesList = cities[country][state];
      citiesList.forEach(city => {
        $('#cities').append(`<option value="${city}">${city}</option>`);
      });
    });
  </script>
</body>

</html>