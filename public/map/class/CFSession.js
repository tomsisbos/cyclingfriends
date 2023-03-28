export default class CFSession {

    static apiUrl = '/api/riders/session.php'

    /**
     * Check if connected user has editor rights or higher
     * @param {string} level premium, editor, moderator, admin
     * @returns {Promise} boolean
     */
    static hasRights (level) {
        return new Promise((resolve, reject) => {
            ajaxGetRequest(this.apiUrl + '?has-rights=' + level, (response) => resolve(response))
        } )
    }

    /**
     * Get one session entry
     * @param {string} key name of the key entry to get value of
     * @returns {Promise} corresponding value to the key
     */
    static get (key) {
        return new Promise((resolve, reject) => {
            if (localStorage.getItem(key)) resolve(localStorage.getItem(key))
            else ajaxGetRequest(this.apiUrl + '?get=' + key, (value) => resolve(value))
        } )
    }

    /**
     * Get profile picture of connected user
     * @returns {Promise} src
     */
    static getPropic () {
        return new Promise((resolve, reject) => {
            ajaxGetRequest(this.apiUrl + '?get-propic=true', (src) => resolve(src))
        } )
    }

    static getSession () {
        return new Promise((resolve, reject) => {
            ajaxGetRequest(this.apiUrl + '?get-session=true', (session) => resolve(session))
        } )
    }

}