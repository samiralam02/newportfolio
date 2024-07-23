  // Carousel functionality
  let currentIndex = 0;
  const items = document.querySelectorAll('.carousel-item');

  function showNextSlide() {
      items[currentIndex].style.display = 'none';
      currentIndex = (currentIndex + 1) % items.length;
      items[currentIndex].style.display = 'flex';
  }