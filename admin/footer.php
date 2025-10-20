
<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; 420 Vallarta 2022 </span>
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