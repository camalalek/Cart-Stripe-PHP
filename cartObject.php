<?php
    
include("dbConnect.php");


function set_cart(){
    if (!isset($_COOKIE['CartID'])) {
        $ID = rand(10000,100000);
        include("dbConnect.php");
        $queryID = mysqli_query($connect, "SELECT * FROM cart WHERE cart_id='".$ID."'");

        $queryNoID = mysqli_num_rows($queryID);
        if ($queryNoID == 0) {
            setcookie('CartID' , $ID);
        }else{
            set_cart();
        }
    }
}


function add_cart($cart_id, $cart_qty){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];

    //Stock check
    $stockQuery = mysqli_query($connect, "SELECT product_stock FROM products WHERE product_id='".$cart_id."' LIMIT 1");
    $stockQueryNo = mysqli_num_rows($stockQuery);

    while($stockQueryNo = mysqli_fetch_assoc($stockQuery)) {
        $product_stock = $stockQueryNo['product_stock'];

        if ($product_stock>=$cart_qty && $cart_qty !=0) {
           $addCartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_prod_id='".$cart_id."' && cart_user='".$ID."'");
            $addCartQueryNO = mysqli_num_rows($addCartQuery);
            if ($addCartQueryNO == 0) {
                mysqli_query($connect, "INSERT INTO cart (cart_id, cart_prod_id, cart_qty, cart_user) VALUES (NULL, '".$cart_id."', '".$cart_qty."', '".$ID."')");
            }else{
                edit_cart($cart_id, $cart_qty);
            }
            header("location: cart.php?cartID=".$cart_id); 
            exit();
        }else{
            header("location: products.php?product_id=".$cart_id); 
            exit();
        }
    }
}

function delete_cart($cart_id){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    mysqli_query($connect, "DELETE FROM cart WHERE cart_id='".$cart_id."' && cart_user='".$ID."'");

     header("location: cart.php"); 
}

function edit_cart($cart_id, $cart_qty){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    mysqli_query($connect, "UPDATE cart SET cart_qty='".$cart_qty."' WHERE cart_prod_id='".$cart_id."' && cart_user='".$ID."'"); 

    header("location: cart.php"); 
}

function empty_cart(){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    mysqli_query($connect, "DELETE FROM cart WHERE cart_user='".$_COOKIE['CartID']."'");
}

function cart_length(){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    $CartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
    $CartQueryNO = mysqli_num_rows($CartQuery);
    return $CartQueryNO;
    
}

function cart_qty(){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    $CARTQ = 0;

    $CartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
    $CartQueryNO = mysqli_num_rows($CartQuery);

    while($CartQueryNO = mysqli_fetch_assoc($CartQuery)) {
        $CartQty = $CartQueryNO['cart_qty'];
        $CARTQ+=$CartQty;
    }
    return $CARTQ;
    
}

//Total Price Maths
function cart_total(){
    include("dbConnect.php");

    $ID = $_COOKIE['CartID'];
    $CartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
    $cartQueryNo = mysqli_num_rows($CartQuery);
    $cartTotal = 0.0;

    while ($cartQueryNo = mysqli_fetch_assoc($CartQuery)){
        $product_id = $cartQueryNo['cart_prod_id'];
        $product_qty = $cartQueryNo['cart_qty'];
        $CartQuery2 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$product_id."'");
        $CartQueryNo2 = mysqli_num_rows($CartQuery2);


        while ($CartQueryNo2 = mysqli_fetch_assoc($CartQuery2)){
          $product_price = $CartQueryNo2['product_price'];

          $product_quantitiy = $product_qty;
          $product_calc = $product_quantitiy*$product_price;
          $cartTotal+=$product_calc;
        }
    }

    return $cartTotal*100;
}

//Array Rules
function cart_check(){
    if (isset($_SESSION['CartArray'])) {
        return True;
     }else{
        return False;
     }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Pagination
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$prodPerPage = 12;
$prodstart = ($page - 1) * $prodPerPage;

$result = $connect->query("SELECT count(product_id) AS product_id FROM products WHERE product_archive='1'");
$prodCount = $result->fetch_all(MYSQLI_ASSOC);
$prodTotal = $prodCount[0]['product_id'];
$prodPages = ceil($prodTotal / $prodPerPage);

$prev = $page-1;
$next = $page+1;

$first = ($prodPages-$prodPages)+1;
$last = $prodPages;


//Product listings
if (isset($_GET['cat'])) {
    @$query = mysqli_query($connect, "SELECT * FROM products WHERE product_archive='1' && product_cat='".$_GET['cat']."' LIMIT ".$prodstart.",".$prodPerPage);
}else if (isset($_GET['searchTerm'])) {
    @$query = mysqli_query($connect, "SELECT * FROM products WHERE product_archive='1' &&  product_name LIKE '%".$_GET['searchTerm']."%' LIMIT ".$prodstart.",".$prodPerPage);
}else{
    @$query = mysqli_query($connect, "SELECT * FROM products WHERE product_archive='1' LIMIT ".$prodstart.",".$prodPerPage);
}
@$queryNo = mysqli_num_rows($query);

//Products sections
@$query2 = mysqli_query($connect, "SELECT DISTINCT product_cat FROM products WHERE product_archive='1'");
@$queryNo2 = mysqli_num_rows($query2);

//Product ID check
@$query33 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$_GET['product_id']."'");
@$queryNo33 = mysqli_num_rows($query33);

//Product selected
if (isset($_GET['product_id'])&&$queryNo33!=0) {

    @$query3 = mysqli_query($connect, "SELECT * FROM products WHERE product_archive='1' && product_id='".$_GET['product_id']."'  LIMIT 1");
}else{
    @$query3 = mysqli_query($connect, "SELECT * FROM products WHERE product_archive='1' ORDER BY product_date DESC LIMIT 1");

}
@$queryNo3 = mysqli_num_rows($query3);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Cart ID check
@$query44 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$_GET['product_id']."'");
@$queryNo44 = mysqli_num_rows($query44);

//Cart selected
if (isset($_GET['product_id'])&&$queryNo44!=0) {
    @$query4 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$_GET['product_id']."'");
}
@$queryNo4 = mysqli_num_rows($query4);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Payment Proccesor
if (isset($_POST['stripeToken']) && isset($_POST['stripeEmail'])) {
   
    //stock check
    $ID = $_COOKIE['CartID'];
    $stockCheck = True;

    $stockCartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
    $stockCartQueryNo = mysqli_num_rows($stockCartQuery);
      
    while ($stockCartQueryNo = mysqli_fetch_assoc($stockCartQuery)){
        $stockCart_id = $stockCartQueryNo['cart_prod_id'];
        $stockCart_qty = $stockCartQueryNo['cart_qty'];

        $stockCartQuery2 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$stockCart_id."'");
        $stockCartQueryNo2 = mysqli_num_rows($stockCartQuery2);

        while ($stockCartQueryNo2 = mysqli_fetch_assoc($stockCartQuery2)){
            $stockProduct_qty = $stockCartQueryNo2['product_stock'];

            if ($stockProduct_qty<$stockCart_qty) {
                $stockCheck = False;
            }
        }  
    }

    if ($stockCheck) {
    
        require_once('./config.php');

        $token  = $_POST['stripeToken'];
        $email  = $_POST['stripeEmail'];

        $customer = \Stripe\Customer::create([
            'email' => $email,
            'source'  => $token,
        ]);

        $charge = \Stripe\Charge::create([
          'customer' => $customer->id,
          'amount'   => cart_total(),
          'currency' => 'GBP',
        ]);

        //Adjusts stock of each product
        $ID = $_COOKIE['CartID'];

        $stockCartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
        $stockCartQueryNo = mysqli_num_rows($stockCartQuery);
          
        while ($stockCartQueryNo = mysqli_fetch_assoc($stockCartQuery)){
            $stockCart_id = $stockCartQueryNo['cart_prod_id'];
            $stockCart_qty = $stockCartQueryNo['cart_qty'];

            $stockCartQuery2 = mysqli_query($connect, "SELECT * FROM products WHERE product_id='".$stockCart_id."'");
            $stockCartQueryNo2 = mysqli_num_rows($stockCartQuery2);

            while ($stockCartQueryNo2 = mysqli_fetch_assoc($stockCartQuery2)){
                $stockProduct_qty = $stockCartQueryNo2['product_stock'];
                $stockQtyCalc = $stockProduct_qty - $stockCart_qty;

                //Sets new stock
                mysqli_query($connect, "UPDATE products SET product_stock='$stockQtyCalc' WHERE product_id='$stockCart_id'");
            }  
        } 

        //Sets order to Paid
        mysqli_query($connect, "UPDATE orders SET orderStatus='1' WHERE orderUser='".$_COOKIE['CartID']."' LIMIT 1");

        empty_cart();
        setcookie('CartID', '', 1);


        die('<center><h2>Thank for you\'re purchase. you\'re order is on it\'s way!</h2><hr/>Keep shopping <a href="products.php#prod">here</a></center>');

    }else{

        empty_cart();
        setcookie('CartID', '', 1);


        die('<center><h2>There was an error with the stock check! You\'re cart has been cleared.</h2><hr/>Start shopping <a href="products.php#prod">here</a></center>');

    }
  
}

if (isset($_POST['paySubmit'])) {
    $payError = "";

    if (cart_qty() > 0) {
        $payFirstname = $_POST['firstname'];
        $payLastname = $_POST['lastname'];
        $payEmail = strtolower($_POST['email']);
        $payAddress = $_POST['address'];
        $payAddress2 = $_POST['address-2'];
        $payCountry = $_POST['country'];
        $payCounty = $_POST['county'];
        $payZip = $_POST['zip'];

        $payOrderDate = date('Y-m-d H:i:s');
        $payOrderAddress = "$payAddress, $payAddress2, $payCounty, $payCountry, $payZip";
        $payOrderString = "";

        if ($payFirstname&&$payLastname&&$payEmail&&$payAddress&&$payCountry&&$payCounty&&$payZip) {
            if (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $payEmail)) {

                $ID = $_COOKIE['CartID'];

                $ECartQuery = mysqli_query($connect, "SELECT * FROM cart WHERE cart_user='".$ID."'");
                $ECartQueryNO = mysqli_num_rows($ECartQuery);

                while($ECartQueryNO = mysqli_fetch_assoc($ECartQuery)) {
                    $ECartQty = $ECartQueryNO['cart_qty'];
                    $ECartProdId = $ECartQueryNO['cart_prod_id'];

                    $payOrderString = $payOrderString."".$ECartProdId."-".$ECartQty.",";
                }
                

                //FINAL DATA TRANSFERS
                $message = "An order has been made by ".$payFirstname." ".$payLastname." (".$payEmail."). Please check the dashboard for more details...";
                $headers = "From:" . "update@".$title.".co.uk";
                mail($email,"New Order",$message,$headers);

                mysqli_query($connect, "INSERT INTO orders (orderId, orderFirstname, orderLastname, orderEmail, orderAddress, orderProducts, orderUser, orderDate) VALUES (NULL, '$payFirstname', '$payLastname', '$payEmail', '$payOrderAddress', '$payOrderString', '".$_COOKIE['CartID']."', '$payOrderDate');");
                
                require_once('./config.php');
                $payError = '
                <form action="checkout.php#pay" method="post">
                  <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                          data-key="'.$stripe['publishable_key'].'"
                          data-currency="GBP"
                          data-amount="'.cart_total().'"
                          data-locale="auto"></script>
                </form>
                | Click "Pay with Card" to complete the payment.';

                
            }else{
                $payError = "This email is incorrect!";
            }
        }else{
            $payError = "Please fill in all fields!";
        }
    }else{
        $payError = "Your cart is empty!";
    }
}   

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Function triggers


//Change cart Qty
if (isset($_GET['cartID']) && isset($_GET['cartQty'])) {
  $cartID = $_GET['cartID'];
  $cartQty = $_GET['cartQty'];

  add_cart($cartID, $cartQty);
}

//Remove cart
if (isset($_GET['cart_id'])) {
  $cart_id = $_GET['cart_id'];

  delete_cart($cart_id);
}

//Setting cart ID
set_cart();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>