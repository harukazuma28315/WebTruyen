<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cửa hàng Sách Online</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: #f5f7fa;
    }

    header {
      background: #4a90e2;
      color: #fff;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    .cart-btn {
      background: #fff;
      color: #4a90e2;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .book {
      background: #fff;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.2s;
    }

    .book:hover {
      transform: translateY(-5px);
    }

    .book img {
      width: 100px;
      height: 140px;
      object-fit: cover;
      margin-bottom: 10px;
      border-radius: 6px;
    }

    .book h3 {
      font-size: 16px;
      margin: 5px 0;
      color: #333;
    }

    .book p {
      color: #4a90e2;
      font-weight: bold;
      margin: 8px 0;
    }

    .book button {
      background: #4a90e2;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }

    /* Giỏ hàng */
    .cart {
      position: fixed;
      top: 0;
      right: -400px;
      width: 350px;
      height: 100%;
      background: #fff;
      box-shadow: -4px 0 12px rgba(0,0,0,0.2);
      transition: right 0.3s ease;
      display: flex;
      flex-direction: column;
      z-index: 1000;
    }

    .cart.open {
      right: 0;
    }

    .cart-header {
      background: #4a90e2;
      color: #fff;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .cart-header h2 {
      margin: 0;
      font-size: 18px;
    }

    .close-cart {
      background: none;
      border: none;
      color: #fff;
      font-size: 20px;
      cursor: pointer;
    }

    .cart-items {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
    }

    .cart-item {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }

    .cart-item img {
      width: 50px;
      height: 70px;
      object-fit: cover;
      margin-right: 10px;
      border-radius: 4px;
    }

    .cart-item-info {
      flex: 1;
    }

    .cart-item-info h4 {
      font-size: 14px;
      margin: 0 0 4px;
      color: #333;
    }

    .cart-item-info p {
      margin: 0;
      font-size: 13px;
      color: #4a90e2;
      font-weight: bold;
    }

    .remove-btn {
      background: none;
      border: none;
      font-size: 16px;
      color: red;
      cursor: pointer;
    }

    .cart-footer {
      padding: 15px;
      border-top: 1px solid #eee;
    }

    .cart-footer h3 {
      margin: 0 0 10px;
      font-size: 16px;
      color: #333;
    }

    .checkout-btn {
      background: #4a90e2;
      color: #fff;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
    }
  </style>
</head>
<body>
  <header>
    <h1>📚 Book Store</h1>
    <button class="cart-btn" onclick="toggleCart()">🛒 Giỏ hàng (<span id="cart-count">0</span>)</button>
  </header>

  <div class="container">
    <div class="book">
      <img src="https://picsum.photos/100/140?1" alt="Book 1">
      <h3>Harry Potter</h3>
      <p>120.000đ</p>
      <button onclick="addToCart('Harry Potter', 120000, 'https://picsum.photos/100/140?1')">Thêm vào giỏ</button>
    </div>
    <div class="book">
      <img src="https://picsum.photos/100/140?2" alt="Book 2">
      <h3>Doraemon</h3>
      <p>80.000đ</p>
      <button onclick="addToCart('Doraemon', 80000, 'https://picsum.photos/100/140?2')">Thêm vào giỏ</button>
    </div>
    <div class="book">
      <img src="https://picsum.photos/100/140?3" alt="Book 3">
      <h3>One Piece</h3>
      <p>150.000đ</p>
      <button onclick="addToCart('One Piece', 150000, 'https://picsum.photos/100/140?3')">Thêm vào giỏ</button>
    </div>
  </div>

  <!-- Giỏ hàng -->
  <div class="cart" id="cart">
    <div class="cart-header">
      <h2>🛒 Giỏ hàng</h2>
      <button class="close-cart" onclick="toggleCart()">×</button>
    </div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-footer">
      <h3>Tổng: <span id="cart-total">0</span> đ</h3>
      <button class="checkout-btn">Thanh toán</button>
    </div>
  </div>

  <script>
    let cart = [];

    function toggleCart() {
      document.getElementById("cart").classList.toggle("open");
    }

    function addToCart(name, price, img) {
      cart.push({ name, price, img });
      renderCart();
    }

    function removeFromCart(index) {
      cart.splice(index, 1);
      renderCart();
    }

    function renderCart() {
      let cartItems = document.getElementById("cart-items");
      let cartTotal = document.getElementById("cart-total");
      let cartCount = document.getElementById("cart-count");

      cartItems.innerHTML = "";
      let total = 0;

      cart.forEach((item, index) => {
        total += item.price;
        cartItems.innerHTML += `
          <div class="cart-item">
            <img src="${item.img}" alt="${item.name}">
            <div class="cart-item-info">
              <h4>${item.name}</h4>
              <p>${item.price.toLocaleString()} đ</p>
            </div>
            <button class="remove-btn" onclick="removeFromCart(${index})">×</button>
          </div>
        `;
      });

      cartTotal.textContent = total.toLocaleString();
      cartCount.textContent = cart.length;
    }
  </script>
</body>
</html>
