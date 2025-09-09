$(document).ready(function() {
    // Máscara do telefone
    $('#telefone').mask('(00) 0000-0000');

    // Scrolling suave
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 1000);
        }
    });

    // Menu responsivo
    $('.menu-toggle').on('click', function() {
        $('.menu').toggleClass('active');
        let expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
    });
});

$(document).ready(function() {
    $('.menu-toggle').on('click', function() {
        $('.menu').toggleClass('active');
        let expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
    });
});