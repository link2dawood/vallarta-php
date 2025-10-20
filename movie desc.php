<?php

include 'header.php';




$id = 0;
if(isset($_GET['id'])){
	$id = $_GET['id'];
}


$query = $con->query("SELECT a.*,b.cat_name FROM movies a LEFT JOIN cat b on b.id = a.cat_id WHERE a.movie_id = '$_GET[id]'");
$m = mysqli_fetch_array($query);
?>
<script>
    function callPlayer(func, args) {
        var i = 0,
            iframes = document.getElementsByTagName('iframe'),
            src = '';
        for (i = 0; i < iframes.length; i += 1) {
            src = iframes[i].getAttribute('src');
            if (src && src.indexOf('youtube.com/embed') !== -1) {
                iframes[i].contentWindow.postMessage(JSON.stringify({
                    'event': 'command',
                    'func': func,
                    'args': args || []
                }), '*');
            }
        }
    }
</script>
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

      <title>420 Puerto Vallarta - Cannabis Puerto Vallarta</title><div class="" data-aos=""><br>
	 <div class="container">
		<div align="" class="desktop-view"><a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="images/coursera-banner-1.jpg" style="width:100%"></a></div>
	</div><br>
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-7">
<p align="center" class="mobile-view" style="display:none"><a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="/images/NordVPN.jpg"></a></p>
                        <div class="col-12 ">
                          <h3>420 Puerto Vallarta:<br />
                              <strong>
                                <?= $m['title']; ?>
                          </strong> </h3>
                        </div>
                        <div class="row mb-5">
                          <div class="col-md-7">
  <img src="uploads/<?= $m['thumbnail']; ?>" alt="Images" class="img-fluid">         <br /><br />

<!-- AddToAny BEGIN -->
<div class="a2a_kit a2a_kit_size_32 a2a_default_style" data-a2a-url="http://420.vallartavisitors.com/" data-a2a-title="420 Vallarta">
<a class="a2a_dd" href="https://www.addtoany.com/share"></a>
<a class="a2a_button_facebook"></a>
<a class="a2a_button_twitter"></a></div>
<div align="center">
  <script>
var a2a_config = a2a_config || {};
a2a_config.onclick = 1;
a2a_config.num_services = 4;
</script>
  <script async src="https://static.addtoany.com/menu/page.js"></script>
  <!-- AddToAny END -->     <br />
     </div>
                          </div>

                          <div class="col-md-4 ml-auto">
                                <h3 align="center"><strong>
                                <?= $m['title']; ?>
                                <br />
                                <br />
                                </strong></h3>
                                <div class="modal" id="myModal2">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">×</button>
                                            </div>

                                            <!-- Modal body -->
                                          <div class="modal-body" id="yt-player">
                                                <iframe src="//www.youtube.com/embed/sIFYPQjYhv8?rel=0&enablejsapi=1" allowfullscreen="" width="100%" frameborder="0" height="100%"></iframe>
                                          </div>
                                           <!-- <div class="modal-body">
                                                <div class="embed-responsive embed-responsive-16by9">
                                                    <?php /*if(empty($m['trailer'])){
                                                        echo 'no video found';
                                                    }; */?>
                                                    <iframe class="embed-responsive-item" src="<?/*= $m['trailer']; */?>" allowfullscreen></iframe>
                                                </div>
                                            </div>-->
                                        </div>
                                    </div>
                                </div><form action="cart/cartfunction.php" method="post">
                                                        <div align="center">
                                                          <input type="hidden" value="<?php echo $m['title']?>" name="product_name">
                                                          <input type="hidden" value="<?php echo $m['movie_id']?>" name="movie_id">
                                                          <input type="hidden" value="<?php echo $m['price']?>" name="product_price">
                                                          <input type="hidden" value="<?php echo $m['thumbnail']?>" name="product_image">
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
                                                         
                                                              <br />
                                  </div>
                                  </form>
                                <p>
                                  <br />
                                  <?= $m['short_desc']; ?>
                            </p>
                                
                                <div align="center"><strong>S
                                  <?= $m['price']; ?>
                            </strong></div>
                                <p align="center">                                <span class="row">
                                  <?= $m['long_desc']; ?>
                                  <form action="cart/cartfunction.php" method="post">
                                                        <div align="center">
                                                          <input type="hidden" value="<?php echo $m['title']?>" name="product_name">
                                                          <input type="hidden" value="<?php echo $m['movie_id']?>" name="movie_id">
                                                          <input type="hidden" value="<?php echo $m['price']?>" name="product_price">
                                                          <input type="hidden" value="<?php echo $m['thumbnail']?>" name="product_image">
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
                            </span></p>
                          </div>
                        </div>

                        <div align="center">
                          <!-- Button to Open the Modal -->
                          <!-- Button to Open the Modal -->
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal-<?= $m[0]; ?>"> Watch This </button>
                          <!-- The Modal -->
                          <div class="modal videomodal" id="myModal-<?= $m[0]; ?>">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                  <button type="button" class="close closeme" data-dismiss="modal">×</button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                  <div class="embed-responsive VideoPopup embed-responsive-16by9">
                                    <?php if(empty($m['trailer'])){
                                                                echo 'no video found';
                                                            }; ?>
                                    <iframe class="embed-responsive-item " src="<?= $m['trailer']; ?>" allowfullscreen="allowfullscreen"></iframe>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row site-section">

                            <?php
                            $cat = $m['cat_id'];
                            $querys = $con->query("SELECT a.*,b.cat_name FROM movies a LEFT JOIN cat b on b.id = a.cat_id WHERE a.cat_id = '$cat' LIMIT 3");
                            while ($r = mysqli_fetch_array($querys)) {
                            ?>
                                <div class="col-md-6 col-lg-6 col-xl-4 text-center mb-5">
                                    <img src="uploads/<?= $r['thumbnail']; ?>" alt="Image" width="184" height="272" class="img-fluid w-50 rounded-circle mb-4">
                                    <h2 class="text-black font-weight-light mb-4"><a href="movie_desc.php?id=<?= $r['movie_id'];?>"><?= $r['title']; ?></a></h2>
                                    <p class="mb-4"><?= $r['short_desc']; ?></p>
                                </div>
                            <?php } ?>
                        </div>

                    </div>

                </div>
            </div>
            <div align="center" class="mobile-view" style="display:none"><a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="/images/NordVPN.jpg"></a></div>
            <div class="container">
			
			<div align="" class="desktop-view"><a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank"><IMG border="0"   alt="Start your future with a Data Analysis Certificate." src="images/coursera-banner-1.jpg" style="width:100%"></a></div>
			</div>
      </div> </div>
<script type="text/javascript">
    $('#myModal2').on('hidden.bs.modal', function () {
        callPlayer('yt-player', 'stopVideo');
    });
</script>
<?PHP
include 'footer.php';
?>
