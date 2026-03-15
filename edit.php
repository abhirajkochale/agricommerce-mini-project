<?php
include 'db.php';

$id = $_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM orders WHERE id=$id");
$row = mysqli_fetch_assoc($result);

?>

<form action="update.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

Farmer Name
<input type="text" name="farmer_name" value="<?php echo $row['farmer_name']; ?>">

Crop
<input type="text" name="crop_name" value="<?php echo $row['crop_name']; ?>">

Quantity
<input type="number" name="quantity" value="<?php echo $row['quantity']; ?>">

Price
<input type="number" name="price" value="<?php echo $row['price']; ?>">

Location
<input type="text" name="location" value="<?php echo $row['location']; ?>">

<button type="submit">Update</button>

</form>