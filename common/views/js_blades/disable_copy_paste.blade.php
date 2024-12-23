<script>
    // disable copy paste
    $(".disable-copypaste").on("paste copy cut", function(e)
    {
        if (e.type === "paste" || e.type === "copy" || e.type === "cut")
        {
            return false;
        }
    });

</script>