<!-- Vendor js -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

<!-- Required datatable js -->
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>

<!-- Responsive examples -->
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

<!-- Datatables init -->
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

<!-- Chart.js -->
<script src="<?= base_url(); ?>assets/libs/chart-js/Chart.bundle.min.js"></script>

<script src="<?= base_url(); ?>assets/libs/custombox/custombox.min.js"></script>

<!-- App js -->
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script type="text/javascript">
                        $(document).on("click", ".open-AddBookDialog", function () {
                            var myBookId = $(this).data('id');
                            $(".modal-body #id").val( myBookId );
                        });
                        </script>

</body>
</html>