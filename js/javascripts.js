$(window).ready(function(e) {
    setTimeout(() => {
        $(".loading").fadeOut('slow', () => {
            $(".loading").removeClass("show")
        })
    }, 500)
})

const quContents = Array.from(document.querySelectorAll(".quContent"))
quContents.forEach(quContent => {
    quContent.addEventListener("click", evt => {
        console.log(evt.target.id)
    })
})
