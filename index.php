<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
<title>AgroConnect Orders</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h1>🌾 AgroConnect Crop Orders</h1>

<h2>Add Crop Order</h2>

<form action="insert.php" method="POST">

Farmer Name:
<input type="text" name="farmer_name" required>

Email:
<input type="email" name="email" required>

Crop Name:
<input type="text" name="crop_name" required>

Category:
<select name="category">
<option>Grains</option>
<option>Vegetables</option>
<option>Fruits</option>
</select>

Quantity:
<input type="number" name="quantity" required>

Price:
<input type="number" name="price" required>

Location:
<input type="text" name="location" required>

<br><br>

<button type="submit">Add Order</button>

</form>

<hr>

<h2>Crop Orders</h2>

<table border="1">

<tr>
<th>ID</th>
<th>Farmer</th>
<th>Crop</th>
<th>Quantity</th>
<th>Price</th>
<th>Location</th>
<th>Action</th>
</tr>

<?php

$result = mysqli_query($conn,"SELECT * FROM orders");

while($row = mysqli_fetch_assoc($result))
{
?>

<tr>

<td><?php echo $row['id']; ?></td>
<td><?php echo $row['farmer_name']; ?></td>
<td><?php echo $row['crop_name']; ?></td>
<td><?php echo $row['quantity']; ?></td>
<td><?php echo $row['price']; ?></td>
<td><?php echo $row['location']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
<a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>

</td>

</tr>

<?php
}
?>

</table>

</body>
</html>