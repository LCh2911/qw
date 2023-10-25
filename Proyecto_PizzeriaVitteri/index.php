<?php

include 'config.php'; //Conexión a la bd

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = '¡El nombre de usuario o el correo electrónico ya existen!';
   }else{
      if($pass != $cpass){
         $message[] = '¡Confirmar contraseña no coincidente!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'Registrado con éxito, ¡inicie sesión ahora, por favor!';
      }
   }
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = '¡Cantidad de carrito actualizada!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = '¡Por favor, inicie sesión primero!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'Ya está en el carrito';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = '¡Añadido al carrito!';
      }
   }
}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = '¡Por favor, inicie sesión primero!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.'.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = '¡Pedido realizado con éxito!';
      }else{
         $message[] = '¡Tu carrito vacío!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pizzeria Vitteri</title>

   <!-- Enlace CDN de Font Awesome  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Enlace de archivo CSS personalizado -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<!-- Sección de encabezado  -->

<header class="header">

   <section class="flex">

      <a href="#home" class="logo"> 
      <img src="images/logo.png" height="48" width="auto" alt="Logo de la pizzería"></a>

      <nav class="navbar">
         <a href="#home"><b>Home</b></a>
         <a href="#about"><b>Acerca de</b></a>
         <a href="#menu"><b>Menú</b></a>
         <a href="#order"><b>Orden</b></a>
         <a href="#faq"><b>Preguntas</b></a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>

<!-- Extremos de la sección de encabezado -->

<div class="user-account">

   <section>

      <div id="close-account"><span>Cerrar</span></div>

      <div class="user">
         <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>¡Bienvenid@! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">Cerrar sesión</a>';
               }
            }else{
               echo '<p><span>¡No has iniciado sesión!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>¡Tu carrito está vacío!</span></p>';
            }
         ?>
      </div>

      <div class="flex">

         <form action="user_login.php" method="post">
            <h3>Iniciar sesión</h3>
            <input type="email" name="email" required class="box" placeholder="Ingresa tu correo electrónico" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="Ingresa tu contraseña" maxlength="20">
            <input type="submit" value="Ingresar" name="login" class="btn">
         </form>

         <form action="" method="post">
            <h3>Regístrarse</h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="Ingresa tu nombre de usuario" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="Ingresa tu correo electrónico" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="Ingresa tu contraseña" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="box" placeholder="Confirme su contraseña" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Registrarse" name="register" class="btn">
         </form>

      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>Cerrar</span></div>

      <h3 class="title"> Mis pedidos </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> Fecha: <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> Nombre: <span><?= $fetch_orders['name']; ?></span> </p>
         <p> Número: <span><?= $fetch_orders['number']; ?></span> </p>
         <p> Dirección: <span><?= $fetch_orders['address']; ?></span> </p>
         <p> Forma de pago: <span><?= $fetch_orders['method']; ?></span> </p>
         <p> Total de pedidos: <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> Precio total: <span>S/. <?= $fetch_orders['total_price']; ?></span> </p>
         <p> Estado del pago: <span style="color:<?php if($fetch_orders['payment_status'] == 'pending'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">¡Todavía no hay nada ordenado!</p>';
      }
      ?>

   </section>

</div>

<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>Cerrar</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
          <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
          <form action="" method="post">
             <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
             <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
          </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>¡Tu carrito está vacío!</span></p>';
      }
      ?>

      <div class="cart-total">Monto a pagar : <span>S/. <?= $grand_total; ?></span></div>

      <a href="#order" class="btn">Ordenar ahora</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">

         <div class="slide active">
            <div class="image">
               <img src="images/home-img-1.png" alt="">
            </div>
            <div class="content">
               <h3>Pepperoni</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-2.png" alt="">
            </div>
            <div class="content">
               <h3>Pizza con champiñones</h3> <br>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-3.png" alt="">
            </div>
            <div class="content">
               <h3>Mascarpone Y Champiñones</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

      </div>

   </section>

</div>

<!-- Acerca de los inicios de sección  -->

<section class="about" id="about">

   <h1 class="heading">Sobre nosotros</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/about-1.svg" alt="">
         <h3>Hecho con amor</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Nuestra carta</a>
      </div>

      <div class="box">
         <img src="images/about-2.svg" alt="">
         <h3>Entrega en 30 minutos</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Nuestra carta</a>
      </div>

      <div class="box">
         <img src="images/about-3.svg" alt="">
         <h3>Compartir con amigos</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Nuestra carta</a>
      </div>

   </div>

</section>

<!-- about section ends -->

<!-- Sección Menú  -->

<section id="menu" class="menu">

   <h1 class="heading">Nuestra carta</h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price">S/. <?= $fetch_products['price'] ?></div>
         <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="Añadir al carrito">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">¡Aún no se han añadido productos!</p>';
      }
      ?>

   </div>

</section>

<!-- menu section ends -->

<!-- Sección de pedidos  -->

<section class="order" id="order">

   <h1 class="heading">Pedir ahora</h1>

   <form action="" method="post">

   <div class="display-orders">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
              $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
              $total_products = implode($cart_item);
              echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>¡Tu carrito está vacío!</span></p>';
         }
      ?>

   </div>

      <div class="grand-total"> Monto a pagar : <span>S/. <?= $grand_total; ?></span></div>

      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

      <div class="flex">
         <div class="inputBox">
            <span> Tu nombre :</span>
            <input type="text" name="name" class="box" required placeholder="Ingresa tu nombre" maxlength="20">
         </div>
         <div class="inputBox">
            <span> Tu número :</span>
            <input type="number" name="number" class="box" required placeholder="Ingresa tu número" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
         </div>
         <div class="inputBox">
            <span>Forma de pago</span>
            <select name="method" class="box">
               <option value="Dinero en efectivo">Dinero en efectivo</option>
               <option value="Tarjeta de credito">Tarjeta de crédito</option>
               <option value="yape">Yape</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Línea de dirección 01 :</span>
            <input type="text" name="flat" class="box" required placeholder="Ej. el nº de piso" maxlength="50">
         </div>
         <div class="inputBox">
            <span>Línea de dirección 02 :</span>
            <input type="text" name="street" class="box" required placeholder="Ej. el nombre de la calle" maxlength="50">
         </div>
         <div class="inputBox">
            <span>Código PIN:</span>
            <input type="number" name="pin_code" class="box" required placeholder="Ej. 123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
         </div>
      </div>

      <input type="submit" value="Ordenar ahora" class="btn" name="order">

   </form>

</section>

<!-- order section ends -->

<!-- Sección de preguntas frecuentes  -->

<section class="faq" id="faq">

   <h1 class="heading">Preguntas más frecuentes</h1>

   <div class="accordion-container">

      <div class="accordion active">
         <div class="accordion-heading">
            <span>¿Tienen opciones de bebidas?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Sí, ofecemos una variedad de bebidas, incluyendo jugos, batidos, infusiones y gaseosas.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>¿Cuánto tiempo se tardan en entregar un pedido?</span>
            <i class="fas fa-angle-down"></i>
         </div>
            <p class="accrodion-content">
            El tiempo de entrega puede variar, pero generalmente entregamos en un plazo de 30-45 minutos.
            Los tiempos exactos dependerán de la ubicación y la demanada en ese momento.
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>¿Aceptan pedidos para eventos o fiestas?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            ¡Por supuesto! Estamos encantantados de antender pedidos paa eventos especiales.
            Por favor, comunicate con anticipación para coordinar los detalles.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>¿Puedo personalizar mi pizza con ingredientes adicionales?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
         Claro, ofrecemos opciones de personalización. puedes agregar ingredientes extra a tu pizza, pero ten en cuenta
         que puede haber un costo adicional.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>¿Cómo puedo estar al tanto de las promociones especiales?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
         Siguenos en redes sociales para recibir cupones y enterarte de nuestras promociones especiales.
         </p>
      </div>
   </div>

</section>

<!-- faq section ends -->

<!-- Sección de pie de página  -->

<section class="footer">

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>Número de celular</h3>
         <p>989392704</p>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>Nuestra dirección</h3>
         <p>Av.Egipto #740 La Esperanza</p>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <h3>Horario</h3>
         <p>Lunes a Domingo: 17:00 - 23:00</p>
      </div>

      <div class="box">
         <i class="fab fa-facebook-square"></i>   
         <i class="fab fa-instagram"></i>
         <h3>Redes Sociales</h3>
         <p><a href="https://www.facebook.com/pizzavitteri?mibextid=ZbWKwL" target="_blank">Facebook</a> |
         <a href="https://instagram.com/pizza_vitteri?igshid=MzRlODBiNWFlZA==" target="_blank">Instagram</a></p>
      </div>
   </div>

</section>

<!-- footer section ends -->



















<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>