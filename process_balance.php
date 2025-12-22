<div class="card" style="margin-top:2rem; background: #fffbeb;">
    <h3>Add Balance to Passenger</h3>
    <form method="POST" action="process_balance.php">
        <input type="email" name="p_email" placeholder="Passenger Email" required>
        <input type="number" name="amount" placeholder="Amount ($)" required>
        <button type="submit" class="btn btn-warning">Transfer Funds</button>
    </form>
</div>