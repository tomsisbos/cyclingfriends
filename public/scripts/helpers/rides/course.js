import Helper from '/scripts/helpers/model.js'

export default class RideCourseHelper extends Helper {

    constructor () {
        super()
    }

    static async startGuidance (method) {
        return new Promise (async (resolve, reject) => { 
            if (method == 'pick') {
                await this.openWindow(`
                    地図上にクリックして、チェックポイントを作りましょう。<br><br>
                    
                    地図にクリック：チェックポイント追加<br>
                    チェックポイントに左クリック：情報入力<br>
                    チェックポイントに右クリック：削除
                `)
                resolve(true) 
            } else if (method == 'draw') {
                await this.openWindow(`
                    コース上にクリックして、チェックポイントを作りましょう。<br><br>
                    
                    コースにクリック：チェックポイント追加<br>
                    チェックポイントに左クリック：情報入力<br>
                    チェックポイントに右クリック：削除
                `)
                resolve(true)
            }
        } )
    }

}