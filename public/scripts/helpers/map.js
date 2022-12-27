import Helper from '/scripts/helpers/model.js'

export default class WorldHelper extends Helper {

    constructor () {
        super()
    }

    static async onEditMkpointsStart () {
        return new Promise (async (resolve, reject) => { 
            await this.openWindow(`
                絶景スポット編集モードに切り替えると、ドラッグ＆ドロップ動作で絶景スポットの位置を変更することができます。<br><br>
                元の位置に戻すことが出来ないので、慎重に使いましょう。
            `, {className: 'bg-admin'})
            resolve(true)
        } )
    }

}