<?php
    include '../connection/connection.php';
    // session_start();
    // $admin_id = $_SESSION['user_name'];
    // $user_id = $_SESSION['user_id'];

    // if(!isset($admin_id)){
    //     header('location:../components/login.php');
    // }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:../components/login.php');
    }
    
        //adding product in cart
        if (isset($_POST['add_to_cart'])) {
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $product_price = $_POST['product_price'];
            $product_quantity = $_POST['product_quantity'];
            $product_image = $_POST['product_image'];
          
            $cart_items = json_decode(file_get_contents('cart.json'), true); // Giả sử có một tệp cart.json
          
            $found = false;
            if ($cart_items) {
              foreach ($cart_items as $key => &$item) {
                if ($item['product_id'] == $product_id) {
                  $item['quantity'] += $product_quantity;
                  $found = true;
                  break;
                }
              }
            }
          
            if (!$found) {
              $cart_items[] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'product_price' => $product_price,
                'quantity' => $product_quantity,
                'product_image' => $product_image,
              ];
            }
          
            file_put_contents('cart.json', json_encode($cart_items)); // Cập nhật cart.json
          
            $message[] = 'Sản phẩm đã được thêm thành công vào giỏ hàng';
          }

    //delete product from wishlist
    if(isset($_GET['delete'])){
        $delete_id = $_GET['delete'];
        mysqli_query($conn,"DELETE FROM `wishlist` WHERE wishlist_id = '$delete_id'") or die('query failed');

        header('location:../user/wishlist.php');
    }

    if(isset($_GET['delete_all'])){
        mysqli_query($conn,"DELETE FROM `wishlist` WHERE user_id = '$user_id'") or die('query failed');

        header('location:../user/wishlist.php');
    }
   
?>

<style type="text/css">
    <?php
        include '../CSS/main.css'
    ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <title>Home</title>
</head> 
<body>
    <!-- <div class="line3"></div> -->
    <?php include 'header_guest.php'?>
    <!-- <div class="banner">
        <div class="detail">
            <h1>Danh sách yêu thích của chúng tôi</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="../user/index.php">Trang chủ</a><span>/Danh sách yêu thích</span>
        </div>
    </div> -->

    <div class="line"></div>
    <section class="shop">
        <!-- <h1 class="title">Sản phẩm bán chạy nhất</h1> -->
        <h1 class="title">Những sản phẩm đã được thêm vào danh sách</h1>
        <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '
                        <div class="message">
                            <span>' . $message . '</span>
                            <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                        </div>
                    ';
                }
            }
        ?>

        <div class="box-container">
            <?php
                $grand_total=0;
                $select_wishlist= mysqli_query($conn, "SELECT * FROM `wishlist`") or die('query failed');
                if(mysqli_num_rows($select_wishlist)>0){
                    while($fetch_wishlist = mysqli_fetch_assoc($select_wishlist)){

            ?>
            <form method="post" class="box">
                <img  class="imgwishlist" src="../image/<?php echo $fetch_wishlist['product_image']; ?>">
                <div class="pricewishlist"><?php echo number_format($fetch_wishlist['product_price'], 0, '.', '.'); ?>VNĐ</div>
                <div class="name"><?php echo $fetch_wishlist['product_name']; ?></div>
                <input type="hidden" name="product_id" value="<?php echo $fetch_wishlist['wishlist_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $fetch_wishlist['product_name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $fetch_wishlist['product_price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_wishlist['product_image']; ?>">
                <div class="icon">
                    <a href="../user/view_page.php?pid=<?php echo $fetch_wishlist['wishlist_id'];?>" class="bi bi-eye-fill"></a>
                    <a href="../user/wishlist.php?delete=<?php echo $fetch_wishlist['wishlist_id'];?>" class="bi bi-x" onclick="return confirm('Bạn có muốn xóa sản phẩm khỏi danh sách')"></a>
                    <button type="submit" name="add_to_cart" class="bi bi-cart"></button>
                </div>
            </form>
            <?php
                    $grand_total += $fetch_wishlist['product_price'];
                    }
                }else{
                    echo '<p class="empty">Chưa có sản phẩm nào được thêm!</p>';
                }
            ?>
        </div>
        <div class="wishlist_total">
                <p>Tổng số tiền phải trả: <span><?php echo number_format($grand_total, 0, '.', '.');?></span>VND</p>
                <a href="../user/shop.php" class="btn">Tiếp tục mua sắm</a>
                <a href="../user/wishlist.php?delete_all" class="btn <?php echo ($grand_total)?'':'disabled'?>" onclick="return confirm('Bạn có muốn xóa tất cả sản phẩm trong danh sách')">Xóa tất cả</a>
        </div>
    </section>

    <?php include '../user/footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>
</html>