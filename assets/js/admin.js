jQuery(document).ready(function($) {

    // Toggle sections
    $(document).on("click", ".rss-toggle", function (e) {
        e.preventDefault();
        e.stopPropagation();
    
        const $toggle = $(this);
        const $content = $toggle.next(".rss-content");
    
        // Prevent multiple clicks during animation
        if ($content.is(":animated")) {
          return false;
        }
    
        // Close all other sections first
        $(".rss-toggle").not($toggle).removeClass("rss-open");
        $(".rss-content").not($content).slideUp(300);
    
        // Toggle current section
        $toggle.toggleClass("rss-open");
        $content.slideToggle(300);
    
        return false;
      });

});