import React from 'react'

class Sidebar extends React.Component {

    constructor (props) {
        super(props)
    }

    render () {

        return (
            <div className="flex-shrink-0 p-3 stg-sidebar">

                <div id="accordion">
                    <div className="card">
                        <div className="card-header" id="headingOne">
                            <h5 className="mb-0">
                                <button className="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Account
                                </button>
                            </h5>
                        </div>

                        <div id="collapseOne" className="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <ul className="card-body">
                                <a onClick={() => this.props.changePage('changePassword')}><li>Change password</li></a>
                                <a onClick={() => this.props.changePage('changeEmail')}><li>Change email</li></a>
                            </ul>
                        </div>
                    </div>
                    <div className="card">
                        <div className="card-header" id="headingTwo">
                            <h5 className="mb-0">
                                <button className="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Preferences
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" className="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <ul className="card-body">
                                <a onClick={() => this.props.changePage('privacy')}><li>Privacy</li></a>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        )
    }

}

export default Sidebar