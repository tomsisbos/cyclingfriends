import React from "react"
import CFUtils from "/public/class/utils/CFUtils"

export default function Tag ({ tag }) {

    return (
        <a target="_blank" href="/tag/` + tag + `">
            <div className="popup-tag tag-light">{"#" + CFUtils.getTagString(tag)}</div>
        </a>
    )

}