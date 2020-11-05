<?php

if (cart_length() == 0) {
  echo "
  <li class='list-group-item d-flex justify-content-between lh-condensed' style='display:none;'>
  <div class='panel panel-danger'>
    <div class='panel-heading'><h5>Your cart is empty</h5></div>
    <div class='panel-body'><h6>Check our products <a href='products.php'>here.</a></h6></div>
  </div>
</div>
  <div class='panel panel-default'>
    <div class='panel-heading'></div>
    <div class='panel-body'></div>
  </div>
</li>";

  }else{
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
          $product_id = $CartQueryNo2['product_id'];
          $product_name = ucfirst($CartQueryNo2['product_name']);
          $product_price = $CartQueryNo2['product_price'];
          $product_date = $CartQueryNo2['product_date'];
          $product_image_src = $CartQueryNo2['product_image_src'];
          $product_cat = $CartQueryNo2['product_cat'];
          $product_selection = $CartQueryNo2['product_selection'];

          $product_desc_1 = $CartQueryNo2['product_desc'];
          $product_desc_2 = substr($product_desc_1, 0, 100);

          $product_quantitiy = $product_qty;
          $product_calc = $product_quantitiy*$product_price;
          $cartTotal+=$product_calc;

          echo "
            <a href='cart.php?product_id=$product_id#cartS'><li class='list-group-item d-flex justify-content-between lh-condensed hoverable'>
              <div>
                <h6 class='my-0'>$product_qty x $product_name<br><small class='text-muted'>$product_cat</small></h6>
                <small class='text-muted'>$product_desc_2...</small>
              </div>
              <span class='text-muted'>£".number_format($product_calc,2)."</span>
            </li></a>
          ";
        }
    }  
  } 

?>