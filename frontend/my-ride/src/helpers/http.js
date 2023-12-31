import axios from "axios"

 export const http = () => {

    let options = {
        // baseURL: 'http://localhost/ride/back',
        baseURL: 'https://app.jojoelectricals.com/',
        headers: {}

    }

    if (localStorage.getItem('token')) {

        options.headers.Authorization = `Bearer ${localStorage.getItem('token')}`

    }

    return axios.create(options)
}

export const api_url = 'https://app.jojoelectricals.com'

export const host_name = 'app.jojoelectricals.com'

// export const api_url = 'http://localhost/ride/back'

// export const host_name = 'localhost'

export default http