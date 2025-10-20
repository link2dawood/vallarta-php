<?php
include 'header.php';
?>

        <div class="site-section" data-aos="fade">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="row mb-5">
                            <div class="col-12 ">
                                <h2 class="site-section-heading text-center">420 Lists</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="container-fluid">
                                <div class="swiper-container images-carousel">
                                    <div class="swiper-wrapper">
                                        <?php

                                        $halaman = 6;
                                        $page = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
                                        $mulai = ($page > 1) ? ($page * $halaman) - $halaman : 0;
                                        $cat_id = $_GET['cat'];
                                        if ($cat_id == 'all') {
                                            $result = $con->query("SELECT a.*,b.cat_name FROM movies a left join cat b on b.id = a.cat_id");
                                        } else {
                                            $result = $con->query("SELECT a.*,b.cat_name FROM movies a left join cat b on b.id = a.cat_id where a.cat_id = '$cat_id'");
                                        }
                                        $total = mysqli_num_rows($result);
                                        $pages = ceil($total / $halaman);

                                        if (empty($_GET['keyword'])) {
                                            if ($cat_id == 'all') {
                                                $query = $con->query("SELECT a.*,b.cat_name FROM movies a left join cat b on b.id = a.cat_id order by id desc LIMIT $mulai, $halaman");
                                            } else {
                                                $query = $con->query("SELECT a.*,b.cat_name FROM movies a left join cat b on b.id = a.cat_id where a.cat_id = '$cat_id' order by id desc LIMIT $mulai, $halaman");
                                            }
                                        } else {
                                            $cari = $_GET['keyword'];
                                            $query = $con->query("SELECT a.*,b.cat_name FROM movies a left join cat b on b.id = a.cat_id where a.nama_barang LIKE '%$cari%' order by id desc LIMIT $mulai, $halaman");
                                        }

                                        $no = $mulai + 1;
                                        while ($m = mysqli_fetch_array($query)) {
                                        ?>
                                            <div class="swiper-slide">
                                                <div class="image-wrap">
                                                    <div class="image-info">
                                                        <h2 class="mb-3"><?= $m['title']; ?></h2>
                                                        <a href="movie_detail.php?id=<?= $m['id']; ?>" class="btn btn-outline-white py-2 px-4">Selections</a>
                                                    </div>
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







                        <div class="row">
                            <center>
                                <div class="pagination">
                                    <?php for ($i = 1; $i <= $pages; $i++) { ?>
                                        <a href="?cat=<?php echo $cat_id; ?>&halaman=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php } ?>
                                </div>
                            </center>
                        </div>
                        <!-- /.pagination -->

                    </div>
                </div>
            </div>
        </div>
<?PHP
include 'footer.php';
?>