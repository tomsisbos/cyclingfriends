document.querySelectorAll('.rd-checkpoint').forEach($checkpoint => {
    $checkpoint.addEventListener('mouseenter', () => $checkpoint.style.width = 'auto')
    $checkpoint.addEventListener('mouseleave', () => $checkpoint.style.width = '100px')
} )