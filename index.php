<?php
include 'header.php';
?><title>420 Vallarta Cannabis Marijuana Featured Products</title>
        <div class="site-section p-0" data-aos="fade">
            <div class="container-fluid">
                <div class="row justify-content-center">
                  <div class="col-md-12">
                        <div class="row">
                            <div class="container-fluid">
                                <div class="swiper-container images-carousel">
                                    <div class="swiper-wrapper">
                                        <?php
                                        $query = $con->query("SELECT * FROM movies where featured=1 ORDER BY `pin_unpin_time`DESC");
                                        while ($m = mysqli_fetch_array($query)) {
                                        ?>
                                            <div class="swiper-slide">
                                              <div class="image-wrap">
                                                <div class="image-info">
                                                        <h2 class="mb-3"><?= $m['title']; ?></h2>
                                                    <a href="movie_desc.php?id=<?= $m['movie_id']; ?>" class="btn btn-outline-white py-2 px-4">See 420  </a>                                                  </div>
                                                    <img src="uploads/<?= $m['thumbnail']; ?>" alt="Image">
                                              </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-scrollbar"></div>
                                </div>
                            </div>
                        </div>

                  </div>
                </div>
            </div>
        </div>
</div>
<?PHP
include 'footer.php';
?>
