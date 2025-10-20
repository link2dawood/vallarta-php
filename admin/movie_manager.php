<?php
include 'header.php';

?>
<?php if (isset($_GET['DONE_DELETE'])): ?>
    <div class="alert alert-success">
        Movie deleted successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['ERROR'])): ?>
    <div class="alert alert-danger">
        <?php
        switch ($_GET['ERROR']) {
            case 'INVALID_ID':
                echo "Invalid movie ID.";
                break;
            case 'DB_ERROR':
                echo "An unexpected database error occurred while deleting the movie.";
                break;
            case 'EXCEPTION':
                echo "An unexpected system error occurred.";
                break;
            default:
                echo "An unknown error occurred.";
        }
        ?>
    </div>
<?php endif; ?>


                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">420 Cancun  Manager</h1>
                    <p class="mb-4">Manage 420 Cancun Insert/Update/Delete here....</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <a href="?page=add_movie" class="btn btn-primary btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <span class="text">Add New 420 Cancun </span>
                                </a>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $page = $_GET['page'];
                            if ($page == 'data') {
                                include('movie_data.php');
                            } elseif ($page == 'add_movie') {
                                include('movie_add.php');
                            } elseif ($page == 'edit_movie') {
                                include('movie_edit.php');
                            }
                            ?>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
    <script>
        tinymce.init({
            selector: '#default',
        });
    </script>

    <!--- custom -->
    <script language="javascript">
        var form = document.forms['form1'];
        form.video_type[0].onfocus = function() {
            form.embed.disabled = false;
            form.vupload.disabled = true;
        }
        form.video_type[1].onfocus = function() {
            form.embed.disabled = true;
            form.vupload.disabled = false;
        }
    </script>
</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; 420 Cancun 2024 </span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Bootstrap core JavaScript-->
<script src="../assets/back/vendor/jquery/jquery.min.js"></script>
<script src="../assets/back/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="../assets/back/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../assets/back/js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="../assets/back/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/back/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="../assets/back/js/demo/datatables-demo.js"></script>

<!-- Tiny -->
<script src="../assets/back/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#default',
    });
</script>

<!--- custom -->
<script language="javascript">
    var form = document.forms['form1'];
    form.video_type[0].onfocus = function() {
        form.embed.disabled = false;
        form.vupload.disabled = true;
    }
    form.video_type[1].onfocus = function() {
        form.embed.disabled = true;
        form.vupload.disabled = false;
    }
</script>

</body>

</html>