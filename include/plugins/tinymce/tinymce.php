<script src='{@BASEDIR}include/plugins/tinymce/tinymce.min.js'></script>
<script>
	tinymce.init({
		selector: '#selector',
	    plugins: [
	      'fullpage advlist autolink link image lists charmap print preview hr anchor pagebreak',
	      'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
	      'save table contextmenu directionality emoticons template paste textcolor code'
	    ],
	    menubar: "file",
	    toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons | code',
		convert_urls:false,
		relative_urls:true,
		remove_script_host:false
	});
</script>
<style type="text/css">
textarea {
	width: 100%;
	min-height: 400px;
	margin: 5px 0;
	padding: 3px;
}
</style>