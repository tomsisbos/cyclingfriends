import ChangePassword from "/react_components/user/settings/ChangePassword.js"

'use strict'

export class Board extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            page: 'ChangeAccount'
        }
    }

    render() {
        switch (this.state.page) {
            case 'ChangeAccount': return 'A'
            case 'ChangePassword': return ChangePassword()
            case 'Privacy': return 'C'
        }

        return (
            <button onClick={() => this.setState({ liked: true })}>
                Like
            </button>
        )
    }

}