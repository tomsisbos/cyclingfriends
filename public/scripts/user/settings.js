import Board from "/react_components/user/settings/Board.js"

'use strict'

const domContainer = document.querySelector('#board')
const root = ReactDOM.createRoot(domContainer)
root.render(React.createElement(Board))