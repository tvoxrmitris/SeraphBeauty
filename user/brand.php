<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location:../components/login.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/login.php');
    exit();
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die('Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error());
}

    

    

        //adding product in wishlist
        if(isset($_POST['add_to_wishlist'])){
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $product_image = $_POST['product_image'];
    
            $wishlist_number = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');
    
            $cart_num = mysqli_query($conn,"SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');
    
            if(mysqli_num_rows($wishlist_number) > 0){
                $message[]='Sản phẩm đã tồn tại trong danh sách yêu thích';
            }else if(mysqli_num_rows($cart_num)>0 && mysqli_num_rows($wishlist_number)>0){
                $message[]='Sản phẩm đã tồn tại trong giỏ hàng';
            }else{
                mysqli_query($conn, "INSERT INTO `wishlist`(`user_id`, `pid`, `product_name`, `product_price`, `product_image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
                $message[]='Sản phẩm đã được thêm thành công vào danh sách yêu thích';
            }
        }
    
        //adding product in cart
//adding product in cart
if (isset($_GET['add'])) {
    // Lấy product_id từ chuỗi truy vấn
    $product_id = $_GET['add'];

    // Sử dụng câu lệnh chuẩn bị (prepared statement) để tránh lỗi SQL Injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $fetch_product = $result->fetch_assoc();

        $product_name = $fetch_product['product_name'];
        $product_image = explode(',', $fetch_product['product_image'])[0]; // Lấy hình ảnh đầu tiên
        $quantity = 1; // Số lượng mặc định thêm vào là 1

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();

        if ($cart_result->num_rows > 0) {
            // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $message[] = 'Sản phẩm đã có trong giỏ hàng, số lượng đã được cập nhật!';
        } else {
            // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $user_id, $product_id, $product_name, $quantity, $product_image);
            $stmt->execute();
            $message[] = 'Sản phẩm đã được thêm vào giỏ hàng!';
        }
    } else {
        $message[] = 'Sản phẩm không tồn tại!';
    }
}

        

        // Kết nối cơ sở dữ liệu và các cài đặt khác
        
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            // Thực hiện truy vấn để lấy sản phẩm từ cơ sở dữ liệu dựa trên từ khóa tìm kiếm
            $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `product_name` LIKE '%$search%' OR `product_price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');
        
            if (mysqli_num_rows($search_query) > 0) {
                // Hiển thị sản phẩm tìm kiếm
                while ($fetch_products = mysqli_fetch_assoc($search_query)) {
                    // Hiển thị thông tin sản phẩm, ví dụ:
                    echo '<div>';
                    echo '<img src="../image/' . $fetch_products['product_image'] . '">';
                    echo '<div>' . $fetch_products['product_name'] . '</div>';
                    echo '<div>' . number_format($fetch_products['product_price'], 0, '.', '.') . 'VNĐ</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
            }
        } else {
            // echo '<p class="empty">Vui lòng nhập từ khóa để tìm kiếm sản phẩm.</p>';
        }


        $brand = isset($_GET['brand']) ? mysqli_real_escape_string($conn, $_GET['brand']) : '';


        // Tính tổng số lượng sản phẩm trong subcategory đang được chọn
        $total_products_query = "
            SELECT COUNT(*) AS total_products
            FROM products
            WHERE brand_name = '$brand'
        ";
        $result = mysqli_query($conn, $total_products_query);
        $row = mysqli_fetch_assoc($result);
        $total_products = $row['total_products'] ? $row['total_products'] : 0;
        $sort_by = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'name';

        // Xác định thứ tự sắp xếp
        $sort_query = '';
        switch ($sort_by) {
            case 'price_asc':
                $sort_query = 'ORDER BY product_price ASC';
                break;
            case 'price_desc':
                $sort_query = 'ORDER BY product_price DESC';
                break;
            case 'name_asc':
                $sort_query = 'ORDER BY product_name ASC';
                break;
            case 'name_desc':
                $sort_query = 'ORDER BY product_name DESC';
                break;
            default:
                $sort_query = 'ORDER BY product_name ASC'; // Mặc định sắp xếp theo tên sản phẩm
        }
        $brand = isset($_GET['brand']) ? urldecode($_GET['brand']) : '';

        ?>
        
     
        
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400..700;1,400..700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   


<style type="text/css">
    <?php
        include '../CSS/main.css'
    ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
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
    <?php include '../user/header.php'?>


    <section class="shop">

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

<!-- <div class="about-us">
    <div class="row">
        <div class="box">
            <div class="title">

                <h1>Chào mừng bạn đến với cửa hàng của chúng tôi</h1>
            </div>

        </div>
        <?php
      
        $image_names = array("about.jpg", "about1.jpg", "about2.jpg", "about.jpg", "about1.jpg", "about2.jpg");
        ?>

        <div class="img-about" style="position: relative;"> 
            <?php foreach ($image_names as $index => $image_name) { ?>
                <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>" src="../image/<?php echo $image_name; ?>" data-index="<?php echo $index; ?>">
            <?php } ?>

            
            <div class="dot-about">
                <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                    <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>" data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                <?php } ?>
            </div>
        </div>
    </div>
</div> -->

<script type="text/javascript">

    $('.img-about').slick({
        // dots: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        lazyLoad: 'ondemand',
        // speed: 300,
        slidesToShow: 1,
        adaptiveHeight: true
    });

    </script>

<div class="title">

<h2 style="font-size: 30px";>Sản phẩm đang hoạt động</h2>
</div>
<div class="brand">
    <?php
    // Lấy tên thương hiệu từ tham số URL
    $brand = isset($_GET['brand']) ? mysqli_real_escape_string($conn, $_GET['brand']) : '';

    // Kiểm tra nếu tên thương hiệu đã được cung cấp
    if (!empty($brand)) {
        // Truy vấn để lấy các thương hiệu từ cơ sở dữ liệu khớp với tên thương hiệu cung cấp
        $query = "SELECT DISTINCT brand_name FROM products WHERE brand_name = '$brand'";
        $result = mysqli_query($conn, $query);
    
        // Kiểm tra xem truy vấn có thành công không
        if ($result) {
            // Lấy kết quả dưới dạng mảng kết hợp
            $brands = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
            // Kiểm tra nếu có kết quả
            if (!empty($brands)) {
                // Hiển thị tên thương hiệu
                foreach ($brands as $row) {
                    // Hiển thị tên thương hiệu (giả sử brand_name là trường đúng)
                    echo htmlspecialchars($row['brand_name']) . '<br>';
                }
            } else {
                // Nếu không có kết quả, hiển thị thông báo
                echo htmlspecialchars($brand);
            }
        } else {
            echo 'Lỗi truy vấn cơ sở dữ liệu.';
        }
    
        // Giải phóng kết quả
        mysqli_free_result($result);
    } else {
        echo 'Thương hiệu không được chỉ định.';
    }
    ?>
</div>




<div class="border-wrapper">
    <div class="total-quantity">
        <p><?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
    </div>
    <div class="sort-by">
        <form method="get" action="">
            <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="name_asc" <?php echo ($sort_by == 'name_asc') ? 'selected' : ''; ?>>Tên (A-Z)</option>
                <option value="name_desc" <?php echo ($sort_by == 'name_desc') ? 'selected' : ''; ?>>Tên (Z-A)</option>
                <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Giá (Thấp đến Cao)</option>
                <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Giá (Cao đến Thấp)</option>
            </select>
        </form>
    </div>
</div>
<div class="box-container">
<?php
// Trích xuất tham số brand từ URL

$brand = isset($_GET['brand']) ? mysqli_real_escape_string($conn, $_GET['brand']) : '';

// Cập nhật truy vấn SQL để bao gồm điều kiện lọc theo brand_name

$select_products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'Còn hàng' AND brand_name = '$brand' $sort_query") or die('Truy vấn thất bại: ' . mysqli_error($conn));


if (mysqli_num_rows($select_products) > 0) {
    while ($fetch_products = mysqli_fetch_assoc($select_products)) {

        $image_names = explode(',', $fetch_products['product_image']);

        ?>

        <form method="post" class="box">
        <div class="img-container">
            <?php 
            foreach ($image_names as $index => $image_name) { 
                $class = $index === 0 ? 'main-img' : 'second-img'; ?>
                <img class="<?php echo $class; ?>" src="../image/<?php echo trim($image_name); ?>" data-index="<?php echo $index; ?>">
            <?php } ?>
                    <!-- Thẻ a Thêm vào giỏ hàng -->
                    <a href="?add=<?php echo $fetch_products['product_id']; ?>" class="add-to-cart">Thêm vào giỏ hàng</a>

        </div>

        <div class="priceshop">
            <?php
            // Hiển thị giá cho sản phẩm
            echo number_format($fetch_products['product_price'], 0, '.', '.') . ' VNĐ';
            ?>
        </div>
        <div class="name"><?php echo $fetch_products['product_name']; ?></div>

        <input type="hidden" name="product_id" value="<?php echo $fetch_products['product_id']; ?>">
        <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
        <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">

        </form>
        <?php
    }
} else {
    echo '<p class="empty">Chưa có sản phẩm nào được thêm</p>';
}
?>
</div>



















</section>

<script>
function selectCapacity(event, capacity) {
    var container = event.target.closest('.box');
    var capacityButtons = container.querySelectorAll('.capacity-buttons button');
    capacityButtons.forEach(function(button) {
        button.classList.remove('selected');
    });
    event.target.classList.add('selected');
    var selectedCapacity = capacity;
    container.querySelector('input[name="selectedCapacity"]').value = selectedCapacity; // Cập nhật giá trị dung tích cho trường input ẩn

    var productId = container.querySelector('input[name="product_id"]').value;
    // Gửi selectedCapacity đến server để lấy giá tương ứng từ cơ sở dữ liệu
    $.ajax({
        url: 'get_price.php',
        type: 'POST',
        data: {
            productId: productId,
            selectedCapacity: selectedCapacity
        },
        success: function(response) {
            container.querySelector('.priceshop').innerHTML = response;
        }
    });
}
</script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy tất cả các điểm đánh dấu
        var dots = document.querySelectorAll('.dot');

        // Lặp qua từng điểm đánh dấu và thêm sự kiện click
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                // Lấy chỉ số của điểm đánh dấu được nhấp
                var index = parseInt(dot.getAttribute('data-index'));

                // Lấy ID sản phẩm tương ứng
                var productId = dot.getAttribute('data-product-id');

                // Lấy container của sản phẩm
                var container = dot.closest('.box');

                // Lấy tất cả các ảnh của sản phẩm
                var images = container.querySelectorAll('.imgshop');

                // Ẩn tất cả các ảnh của sản phẩm và bỏ đi lớp 'active' khỏi các điểm đánh dấu
                images.forEach(function(image) {
                    image.classList.add('hidden');
                });
                dots.forEach(function(d) {
                    d.classList.remove('active');
                });

                // Hiển thị ảnh tương ứng với điểm đánh dấu được nhấp và thêm lớp 'active' cho điểm đánh dấu đó
                images[index].classList.remove('hidden');
                dot.classList.add('active');
            });
        });
    });
</script>



<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
  chat-title="Xin chào!"
  agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2"
  language-code="en"
></df-messenger>



<?php include '../user/footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>
</html>