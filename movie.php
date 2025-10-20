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

<title>420 Puerto Vallarta - Cannabis Puerto Vallarta</title>

<div class="site-section" data-aos="">
  <div class="container">
    <div class="desktop-view">
      <a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank">
        <img border="0" alt="Start your future with a Data Analysis Certificate." src="images/coursera-banner-1.jpg" style="width:100%">
      </a>
    </div>
  </div><br>

  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-7">
        <div class="row mb-5">
          <div class="col-12 ">
            <h2 class="site-section-heading text-center">420 Puerto Vallarta Product List</h2>
            <div align="center" class="mobile-view" style="display:none">
              <a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank">
                <img border="0" alt="Start your future with a Data Analysis Certificate." src="/images/NordVPN.jpg">
              </a>
            </div>
          </div>
        </div>

        <?php
        $pagination = '';
        if (isset($_GET['cat']) || isset($_GET['keyword']) || isset($_GET['reg']) || isset($_GET['grp'])) {
          ?>
          <div class="row">
            <?php
            $halaman = 12;
            $page = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
            $mulai = ($page > 1) ? ($page * $halaman) - $halaman : 0;

            if (isset($_GET['cat'])) {
              $cat_id = $_GET['cat'];
              $result = $con->query("SELECT a.*, b.cat_name FROM movies a LEFT JOIN cat b ON b.id = a.cat_id WHERE a.cat_id = '$cat_id'");
              $total = mysqli_num_rows($result);
              $pages = ceil($total / $halaman);
              $query = $con->query("SELECT a.*, b.cat_name FROM movies a LEFT JOIN cat b ON b.id = a.cat_id WHERE a.cat_id = '$cat_id' ORDER BY movie_id DESC LIMIT $mulai, $halaman");
              $pagination = 'cat=' . $cat_id;
            }

            if (isset($_GET['grp'])) {
              $grp_id = $_GET['grp'];
              $result = $con->query("SELECT * FROM movies LEFT JOIN grp ON movies.group_id = grp.id WHERE grp.id = '$grp_id'");
              $total = mysqli_num_rows($result);
              $pages = ceil($total / $halaman);
              $query = $con->query("SELECT * FROM movies LEFT JOIN grp ON movies.group_id = grp.id JOIN cat ON movies.cat_id = cat.id WHERE grp.id = '$grp_id' ORDER BY movies.movie_id DESC LIMIT $mulai, $halaman");
              $pagination = 'grp=' . $grp_id;
            }

            if (isset($_GET['reg'])) {
              $reg_id = $_GET['reg'];
              $result = $con->query("SELECT * FROM movies LEFT JOIN reg ON movies.region_id = reg.id WHERE reg.id = '$reg_id'");
              $total = mysqli_num_rows($result);
              $pages = ceil($total / $halaman);
              $query = $con->query("SELECT * FROM movies LEFT JOIN reg ON movies.region_id = reg.id JOIN cat ON movies.cat_id = cat.id WHERE reg.id = '$reg_id' ORDER BY movies.movie_id DESC LIMIT $mulai, $halaman");
              $pagination = 'reg=' . $reg_id;
            }

            if (isset($_GET['keyword'])) {
              $keyword = $_GET['keyword'];
              $result = $con->query("SELECT * FROM movies LEFT JOIN grp ON movies.group_id = grp.id WHERE title LIKE '%$keyword%' OR long_desc LIKE '%$keyword%'");
              $total = mysqli_num_rows($result);
              $pages = ceil($total / $halaman);
              $query = $con->query("SELECT * FROM movies LEFT JOIN grp ON movies.group_id = grp.id JOIN cat ON movies.cat_id = cat.id WHERE title = '$keyword' OR title LIKE '%$keyword%' OR long_desc LIKE '%$keyword%' ORDER BY CASE WHEN title = '$keyword' THEN 1 WHEN title LIKE '$keyword%' THEN 2 WHEN title LIKE '%$keyword%' THEN 3 ELSE 4 END LIMIT $mulai, $halaman");
              $pagination = 'keyword=' . $keyword;
            }

            while ($m = mysqli_fetch_array($query)) {
              ?>
              <div class="col-md-6 col-lg-6 col-xl-4 text-center mb-5 mb-lg-5">
                <div class="h-100 p-4 p-lg-5 bg-light site-block-feature-7">
                  <img src="uploads/<?= $m['thumbnail']; ?>" style="max-width:180px" class="mb-2">
                  <h3 class="text-black h4"><a href="movie_desc.php?id=<?= $m['movie_id']; ?>"><?= $m['title']; ?></a></h3>
                  <p><?= $m['short_desc']; ?></p>
                  <p><strong class="font-weight-bold text-primary"><?= $m['cat_name']; ?></strong></p>
                </div>
              </div>
              <?php
            }
            ?>
          </div>

          <div class="row">
            <div class="pagination">
              <?php for ($i = 1; $i <= $pages; $i++) { ?>
                <a href="?<?= $pagination; ?>&halaman=<?= $i; ?>"><?= $i; ?></a>
              <?php } ?>
            </div>
          </div>

          <?php
        } else {
          ?>
          <div class="row">
            <?php
            $halaman = 12;
            $page = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
            $mulai = ($page > 1) ? ($page * $halaman) - $halaman : 0;
            $result = $con->query("SELECT * FROM movies LEFT JOIN cat ON movies.cat_id = cat.id LEFT JOIN grp ON movies.group_id = grp.id ORDER BY movie_id DESC");
            $total = mysqli_num_rows($result);
            $pages = ceil($total / $halaman);
            $query = $con->query("SELECT * FROM movies LEFT JOIN cat ON movies.cat_id = cat.id LEFT JOIN grp ON movies.group_id = grp.id ORDER BY movie_id DESC LIMIT $mulai, $halaman");

            while ($m = mysqli_fetch_array($query)) {
              $region_id = $m['region_id'];
              $reg_res = mysqli_query($con, "SELECT * FROM reg WHERE id = $region_id");
              $reg_row = mysqli_fetch_array($reg_res);
              ?>
              <div class="col-md-6 col-lg-6 col-xl-4 text-center mb-5 mb-lg-5">
                <div class="h-100 p-4 p-lg-5 bg-light site-block-feature-7">
                  <img src="uploads/<?= $m['thumbnail']; ?>" style="max-width:180px" class="mb-2">
                  <h3 class="text-black h4"><a href="movie_desc.php?id=<?= $m['movie_id']; ?>"><?= $m['title']; ?></a></h3>
                  <p><?= $m['short_desc']; ?></p>
                  <p><strong class="font-weight-bold text-primary"><?= $m['cat_name']; ?></strong></p>
                  <p><strong class="font-weight-bold text-primary"><?= $m['group_name']; ?></strong></p>
                  <p><strong class="font-weight-bold text-primary"><?= $reg_row['region_name']; ?></strong></p>
                </div>
              </div>
              <?php
            }
            ?>
          </div>

          <div class="row">
            <div class="pagination">
              <?php for ($i = 1; $i <= $pages; $i++) { ?>
                <a href="?halaman=<?= $i; ?>"><?= $i; ?></a>
              <?php } ?>
            </div>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </div><br>

  <div align="center" class="mobile-view" style="display:none">
    <a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank">
      <img border="0" alt="Start your future with a Data Analysis Certificate." src="/images/NordVPN.jpg">
    </a>
  </div>

  <div class="container">
    <div class="desktop-view">
      <a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=51582&url_id=902" target="_blank">
        <img border="0" alt="Start your future with a Data Analysis Certificate." src="images/coursera-banner-1.jpg" style="width:100%">
      </a>
    </div>
  </div>
</div>

<?php
include 'footer.php';
?>
