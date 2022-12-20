// Some random colors
const colors = ["#B3DFE3", "#EEEF9C", "#F7B2C4", "#6CC6AA"]

const numBalls = 50
const shapes = []
const classes = ['ball', 'square', 'triangle', 'star']

// Create shapes
let shapesContainer = document.createElement("div")
shapesContainer.className = 'shapes-container'
for (let i = 0; i < numBalls; i++) {
	let shape = document.createElement("div")
	shape.className = 'shape ' + classes[Math.floor(Math.random() * classes.length)]
	shape.style.background = colors[Math.floor(Math.random() * colors.length)]
	shape.style.left = `${Math.floor(Math.random() * 100)}vw`
	shape.style.top = `${Math.floor(Math.random() * 100)}vh`
	shape.style.transform = `scale(${Math.random()})`
	shape.style.width = `${Math.random() * 6 + 1}em`
	shape.style.height = shape.style.width

	shapes.push(shape)
    shapesContainer.appendChild(shape)
}
document.body.after(shapesContainer)

// Keyframes
shapes.forEach( (el, i, ra) => {
	let to = {
		x: Math.random() * (i % 2 === 0 ? -3 : 3),
		y: Math.random() * 2
	}

	let anim = el.animate( [
			{ transform: "translate(0, 0)" },
			{ transform: `translate(${to.x}rem, ${to.y}rem)` }
		],
		{
			duration: (Math.random() + 1) * 2000, // random duration
			direction: "alternate",
			fill: "both",
			iterations: Infinity,
			easing: "ease-in-out"
		}
	)
} )