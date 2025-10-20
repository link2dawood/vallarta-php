<?php
include 'header.php';
?>
<style>
	@media screen and (max-width: 776px) {
	  .mobile-view {
		display: block !important;
	  }
	}
	@media screen and (max-width: 776px) {
	  .desktop-view {
		display: none !important;
	  }
	}
</style>
        <div class="site-section" data-aos="">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="row mb-3">
                            <h2>
                              <?php
                            $query = $con->query("SELECT a.*,b.cat_name FROM movies a LEFT JOIN cat b ON b.id = a.cat_id WHERE a.movie_id = '$_GET[id]'");
                            $m = mysqli_fetch_array($query);
                            ?>
                            </h2>
                            <h2><?= $m['title']; ?>
                              <br />
                              <br /> 
                            <img src="uploads/<?= $m['thumbnail']; ?>" alt="Images" class="img-fluid" /></h2>
                      </div>
                       
<div align="center">
  <script>
var a2a_config = a2a_config || {};
a2a_config.onclick = 1;
a2a_config.num_services = 4;
</script>
  <script async src="https://static.addtoany.com/menu/page.js"></script>
  <!-- AddToAny END --></p>
                         
                      </div>
                        <div class="row">
                            <p>
                              <?php $m['long_desc']; ?>
                              <br />
                          </p>
                        </div>
						<div class="row">
                          <p><br />
						    $
					        <?= $m['price']; ?>
                          </p>
					  </div>
						<form action="cart/cartfunction.php" method="post">
                                  <div align="center">
                                    <input type="hidden" value="<?php echo $m['title']?>" name="product_name" />
                                    <input type="hidden" value="<?php echo $m['movie_id']?>" name="movie_id">
                                    <input type="hidden" value="<?php echo $m['price']?>" name="product_price" />
                                    <input type="hidden" value="<?php echo $m['thumbnail']?>" name="product_image" />
                                    <?php
                                                            if(($m['unit'] <= 0)|| ($m['unit'] == null))
                                                            {
                                                              ?>
                                                               <input type="submit" disabled="disabled" value="Item is currently out of stock" name="add_to_cart" class="btn btn-danger">
                                                              <?php
                                                            }else {
                                                              ?>
                                                                 <input type="submit" value="add to cart" name="add_to_cart" class="btn btn-danger">
                                                              <?php
                                                            }
                                                          ?>
                          </div>
                      </form>
 <div align="center">
                          <p>   <!-- AddToAny BEGIN -->
<div class="a2a_kit a2a_kit_size_32 a2a_default_style" data-a2a-url="http://420.vallartavisitors.com/" data-a2a-title="420 Vallarta">
<a class="a2a_dd" href="https://www.addtoany.com/share"></a>
<a class="a2a_button_facebook"></a>
<a class="a2a_button_twitter"></a></div>
 <p>
   <?php if ($m['video_type'] == '2') { ?>
 </p>
 <p>
       <video style="width:100%" controls>
         <source src="uploads/<?= $m['video']; ?>" type="video/mp4">
         Your browser does not support HTML5 video.       </video>
       <?php } else if ($m['video_type'] == '1') { ?>
 </p>
 <div class="row">
                                    <?= $m['video']; ?>
        </div>
                                <p>
                                  <?php } ?>
                                  <br />
                              </p>
                                <p>&nbsp;                                 </p>
                                <p class="mobile-view" style="display:none"><a href="https://click.linksynergy.com/fs-bin/click?id=Co9iEgnoXcA&offerid=759505.377&subid=0&type=4"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="https://ad.linksynergy.com/fs-bin/show?id=Co9iEgnoXcA&bids=759505.377&subid=0&type=4&gridnum=16"></a>
                            <br />
                            <br />
                            </p>
                    </div>
					
                </div>
            </div>
			
			<div class="container">
			
			<div align="" class="desktop-view"><a href="https://click.linksynergy.com/fs-bin/click?id=Co9iEgnoXcA&offerid=759505.377&subid=0&type=4"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="images/coursera-banner-1.jpg" style="width:100%"></a></div>
			</div>
        </div>
    <div id="myModal1" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <a href="<?= $m['ad_link']; ?>">
                        <img src="uploads/<?= $m['ad_img']; ?>" alt="" style="width:100%">
                    </a>
                </div>
            </div>
        </div>
    </div>
    <head>
        <!--  Essential META Tags -->

        <meta property="og:title" content="<?= $m['title']; ?>">
        <meta property="og:description" content="<?php @$m['long_desc`']; ?>">
        <meta property="og:image" content="http://420.vallartavisitors.com/uploads/<?= $m['thumbnail']; ?>">
        <meta property="og:url" content="http://420.vallartavisitors.com/movie_detail.php?id='<?= $m['id']; ?>">
        <meta name="twitter:card" content="summary_large_image">


        <!--  Non-Essential, But Recommended -->

        <meta property="og:site_name" content="420.vallartavisitors.com">
        <meta name="twitter:image:alt" content="<?= $m['title']; ?>">


        <!--  Non-Essential, But Required for Analytics -->

        <meta property="fb:app_id" content="920213225496176" />
        <meta name="twitter:site" content="@420vallarta">
    </head>
	
<?PHP
include 'footer.php';
?>