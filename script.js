
document.addEventListener('DOMContentLoaded', function () {
  const images = document.querySelectorAll('.myslideshow');
  let currentIndex = 0;
  const duration = 3000; // 3000 milliseconds = 3 seconds

  function showNextImage() {
      // Remove the 'active' class from the current image
      images[currentIndex].classList.remove('active');
      
      // Calculate the index of the next image
      currentIndex = (currentIndex + 1) % images.length;
      
      // Add the 'active' class to the next image
      images[currentIndex].classList.add('active');
  }

  // Initially display the first image
  images[currentIndex].classList.add('active');

  // Change the image every 'duration' milliseconds
  setInterval(showNextImage, duration);
});


    document.addEventListener('DOMContentLoaded', function() {
        var faqBoxes = document.querySelectorAll('.faqbox');
        faqBoxes.forEach((faqBox, index) => {
            faqBox.addEventListener('click', function() {
                var answer = faqBoxes[index].nextElementSibling;
                answer.classList.toggle('open');
            });
        });
    });
