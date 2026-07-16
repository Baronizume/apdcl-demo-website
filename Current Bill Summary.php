<?php
$currentBill = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1
");

$bill = mysqli_fetch_assoc($currentBill);
?>

<!-- Current Bill Summary -->

<div class="card shadow-lg border-0 mb-4">

    <div class="card-header bg-primary text-white">

        <h4 class="mb-0">
            <i class="fas fa-file-invoice-dollar"></i>
            Current Bill Summary
        </h4>

    </div>

    <div class="card-body">

<?php if($bill){ ?>

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>
<th width="45%">Bill Number</th>
<td>APDCL-<?= str_pad($bill['id'],6,"0",STR_PAD_LEFT); ?></td>
</tr>

<tr>
<th>Billing Month</th>
<td><?= htmlspecialchars($bill['month']); ?></td>
</tr>

<tr>
<th>Bill Date</th>
<td><?= date("d-m-Y",strtotime($bill['bill_date'])); ?></td>
</tr>

<tr>
<th>Units Consumed</th>
<td><?= $bill['units']; ?> Units</td>
</tr>

</table>

</div>

<div class="col-md-6">

<table class="table table-bordered">

<tr>
<th>Energy Charge</th>
<td>₹ <?= number_format($bill['energy_charge'],2); ?></td>
</tr>

<tr>
<th>Fixed Charge</th>
<td>₹ <?= number_format($bill['fixed_charge'],2); ?></td>
</tr>

<tr>
<th>Electricity Duty</th>
<td>₹ <?= number_format($bill['electricity_duty'],2); ?></td>
</tr>

<tr>
<th>Subsidy</th>
<td class="text-success">
- ₹ <?= number_format($bill['subsidy'],2); ?>
</td>
</tr>

<tr class="table-warning">

<th>Total Bill</th>

<th class="text-danger fs-5">
₹ <?= number_format($bill['total_bill'],2); ?>
</th>

</tr>

<tr>

<th>Status</th>

<td>

<?php

if($bill['status']=="Paid"){

echo "<span class='badge bg-success'>Paid</span>";

}else{

echo "<span class='badge bg-danger'>Pending</span>";

}

?>

</td>

</tr>

</table>

</div>

</div>

<div class="text-center mt-4">

<a href="view_bill.php?id=<?= $bill['id']; ?>" class="btn btn-primary">
<i class="fas fa-eye"></i> View Bill
</a>

<a href="download_bill.php?id=<?= $bill['id']; ?>" class="btn btn-success">
<i class="fas fa-download"></i> Download Bill
</a>

<?php if($bill['status']!="Paid"){ ?>

<a href="payment.php?id=<?= $bill['id']; ?>" class="btn btn-warning">
<i class="fas fa-credit-card"></i> Pay Bill
</a>

<?php } ?>

</div>

<?php } else { ?>

<div class="alert alert-warning text-center">

No bills available.

</div>

<?php } ?>

</div>

</div>