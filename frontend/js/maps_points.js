(function($) {
    $(document).ready(function() {
        $('.drag_element').hover((e) => {
            var data = $(e.target).closest('.point').attr('data-html');
            var html = "<div class='tooltip'><h4>" + data.split(",")[0] + "</h4><div class='summary'>" + data.split(",")[1] + "</div></div>";
            $(e.target).closest('.drag_element').append(html)
        }, (e) => {
            $(e.target).closest('.drag_element').find('.tooltip').remove();
        })

        $('.drag_element').click((e) => {
            $('.wrap_svl').find('.drag_element').removeClass('md-show');
            var id = $(e.target).closest('.drag_element').attr('data-modal');
            id ? $('#modal' + id).addClass('md-show') : null;
            if (id) {
                var slideIndex = 1;
                showSlides(slideIndex);

                $('.prev').on('click', () => {
                    showSlides(slideIndex += -1);
                })
                $('.next').on('click', () => {
                    showSlides(slideIndex += 1);
                })

                var timer = null;

                function startSetInterval() {
                    timer = setInterval(() => {
                        slideIndex++;
                        showSlides(slideIndex);
                    }, 3000)
                }
                startSetInterval();

                $('#slideshow-container').hover(function(ev) {
                    clearInterval(timer);
                }, function(ev) {
                    startSetInterval();
                });

                $('.currentSlide').on('click', (e) => {
                    var id = $(e.target).attr('data-id');
                    showSlides(slideIndex = id);
                })

                function showSlides(n) {
                    var i;
                    var slides = document.getElementById("slideshow" + id).getElementsByClassName("slide");
                    var dots = document.getElementById("slideshow" + id).getElementsByClassName("dot");
                    if (n > slides.length) { slideIndex = 1 }
                    if (n < 1) { slideIndex = slides.length }
                    for (i = 0; i < slides.length; i++) {
                        slides[i].style.display = "none";
                    }
                    for (i = 0; i < dots.length; i++) {
                        dots[i].className = dots[i].className.replace(" active", "");
                    }
                    slides[slideIndex - 1].style.display = "block";
                    dots[slideIndex - 1].className += " active";
                }

                $('.btn-close').click((e) => {
                    $(e.target).closest('.md-modal').removeClass('md-show');
                });
                $('.md-overlay').on('click', function(e) {
                    $('.md-modal').removeClass('md-show');
                    e.stopPropagation();
                });
            }
        })
    })
})(jQuery)