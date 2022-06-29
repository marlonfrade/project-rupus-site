 var acc = document.getElementsByClassName('drop-box');
 for(let i = 0; i < acc.length; i++) {
     acc[i].addEventListener('click', function() {
         this.classList.toggle('active');
         var panel = this.nextElementSibling;
         if(panel.style.maxHeight) {
             panel.style.maxHeight = null;
         }   else {
             panel.style.maxHeight = panel.scrollHeight + 'px'
         }
     })
 }


 function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

const target = document.querySelectorAll('[data-anime]')
const animationClass = 'animate';

function animeScroll() {
    const windowTop = window.pageYOffset + ((window.innerHeight * 8) / 10);
    console.log(windowTop)
    target.forEach(function(element) {
        if((windowTop) > element.offsetTop) {
            element.classList.add(animationClass);
        }

    })
}

if(target.length) {
window.addEventListener('scroll', debounce(function() {
    animeScroll();
    
    }, 50 ));
}




