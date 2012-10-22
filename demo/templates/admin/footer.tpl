<script>
    apply_ajax();


//    var fancybox_editors = new Array();
    function checkEditor() {
        $('textarea').each(function() {
            if ($(this).attr('display') == 'editor') {
                fancybox_editors[fancybox_editors.length] = KindEditor.create(this, { afterBlur:function() { this.sync(); }});
            }
        });
    }

    checkEditor();
</script>

</body>
</html>
