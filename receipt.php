<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Shop Receipt</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1a3c3c; 
            padding: 20px;
        }

        .content {
            flex-grow: 1;
            background-color: #f5e8c7; 
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #1a3c3c;
        }

        .receipt {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #1a3c3c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar"></div>
        <div class="content">
            <h1>Receipt</h1>
            <div class="receipt">
                <p><strong>Order ID:</strong> <span id="orderId">ORD-2025-0411-001</span></p>
                <p><strong>Date:</strong> <span id="date">April 11, 2025</span></p>
                <p><strong>Customer:</strong> John Doe</p>
                <p><strong>Email:</strong> john.doe@gmail.com</p>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="items">
                       
                    </tbody>
                </table>
                <p><strong>Subtotal:</strong> $<span id="subtotal">0.00</span></p>
                <p><strong>Tax (5%):</strong> $<span id="tax">0.00</span></p>
                <p><strong>Total:</strong> $<span id="total">0.00</span></p>
            </div>
        </div>
    </div>

    <script>
       
        const items = [
            { name: "Gold Necklace", price: 150.00, quantity: 1 },
            { name: "Silver Ring", price: 75.00, quantity: 2 }
        ];

        const itemsBody = document.getElementById('items');
        const subtotalSpan = document.getElementById('subtotal');
        const taxSpan = document.getElementById('tax');
        const totalSpan = document.getElementById('total');

        
        items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>$${(item.price * item.quantity).toFixed(2)}</td>
            `;
            itemsBody.appendChild(row);
        });

       
        let subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        let tax = subtotal * 0.05;
        let total = subtotal + tax;

        subtotalSpan.textContent = subtotal.toFixed(2);
        taxSpan.textContent = tax.toFixed(2);
        totalSpan.textContent = total.toFixed(2);
    </script>
</body>
</html>