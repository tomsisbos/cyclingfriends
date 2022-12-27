import Helper from '/scripts/helpers/model.js'

export default class EditRouteHelper extends Helper {

    constructor () {
        super()
    }

    static async startGuidance () {
        return new Promise (async (resolve, reject) => { 
            await this.openWindow(`
                ポイントを動かすと、それに繋がる全区間がリドロー（再描画）されます。<br><br>
                そのため、編集作業を開始する前に、固めたい区間を<strong>ウェイポイントで適切に区切っておく</strong>ことをおススメします。
            `)
            resolve(true)
        } )
    }

}