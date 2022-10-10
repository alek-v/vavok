<script src='{@HOMEDIR}}include/js/tinymce/tinymce.min.js'></script>
<script>
    tinymce.init({
        selector: '#selector',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
        imagetools_cors_hosts: ['picsum.photos'],
        menubar: 'file edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
        toolbar_sticky: true,
        convert_urls:false,
        relative_urls:true,
        remove_script_host:false
    });
</script>
<style>
    textarea {
        width: 100%;
        min-height: 400px;
        margin: 5px 0;
        padding: 3px;
    }
</style>