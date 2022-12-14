import React from 'react'

class Sidebar extends React.Component {

    constructor (props) {
        super(props)
        this.state = {
            active: null
        }
    }

    changePage (page) {
        this.setState({active: page})
        this.props.changePage(page)
    }

    render () {

        return (
            <div className="stg-sidebar container flex-shrink-0">

                <div id="accordion">
                    <div className="stg-title-card" id="headingOne">
                        <h5 className="mb-0">
                            <div className="stg-title" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Account
                            </div>
                        </h5>
                    </div>

                    <div id="collapseOne" className="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                        <ul className="card-body">
                            <a onClick={() => this.changePage('changePassword')} className={this.state.active == 'changePassword' ? 'active' : ''}><li>Change password</li></a>
                            <a onClick={() => this.changePage('changeEmail')} className={this.state.active == 'changeEmail' ? "active" : ''}><li>Change email</li></a>
                        </ul>
                    </div>
                    <div className="stg-title-card" id="headingTwo">
                        <h5 className="mb-0">
                            <div className="stg-title collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Preferences
                            </div>
                        </h5>
                    </div>
                    <div id="collapseTwo" className="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <ul className="card-body">
                            <a onClick={() => this.changePage('privacy')} className={this.state.active == 'privacy' ? "active" : ''}><li>Privacy</li></a>
                        </ul>
                    </div>
                </div>

            </div>
        )
    }

}

export default Sidebar