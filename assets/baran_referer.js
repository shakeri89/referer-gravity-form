(function() {
    function getURLParameter(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    function getUrlParams(url) {
        const params = new URLSearchParams(url.split('?')[1] || '');
        return {
            utm_source: params.get("utm_source"),
            utm_medium: params.get("utm_medium"),
            utm_campaign: params.get("utm_campaign"),
            source1: params.get("source1"),
            medium1: params.get("medium1"),
            ad_group: params.get("adgroup_id"),
            matchtype: params.get("matchtype"),
            keyword: params.get("keyword"),
            campaign_content: params.get("campaign_content"),
            campaign_term: params.get("campaign_term"),
            campaign_id: params.get("campaign_id"),
        };
    }

    function setRefererData() {
        let referer = localStorage.getItem('brr_referer') || false;
        let medium = localStorage.getItem('brr_medium') || false;
        let campaign = localStorage.getItem('brr_campaign') || false;
        let updateData = false;
        const urlParams = getUrlParams(window.location.href);

        const referrerFromURL = getURLParameter('utm_source') || getURLParameter('source1');
        const mediumFromURL = getURLParameter('utm_medium') || getURLParameter('medium1');
        const campaignFromURL = getURLParameter('utm_campaign');

        // Set data if URL params are present
        if (referrerFromURL) {
            referer = referrerFromURL;
            medium = mediumFromURL || false;
            campaign = campaignFromURL || false;
            updateData = true;
        } else if (document.referrer && !document.referrer.includes('baransys.com')) {
            referer = new URL(document.referrer).host.replace(/^www\./, '');
            updateData = true;
        }

        // Store in localStorage if data needs to be updated
        if (updateData) {
            deleteStoredCookies(Object.keys(urlParams));
            setSecureCookie("brr_referer", referer, 1);
            setSecureCookie("brr_medium", medium, 1);
            setSecureCookie("brr_campaign", campaign, 1);
        }
    }

    function getFormData()
    {
        jQuery.ajax({
            url: brrSettings.ajaxUrl,
            method: "POST",
            data: {
                action: brrSettings.getFormsInfoAction,
                nonce: brrSettings.getFormsInfoNonce
            },
            success: res => {
                // Response from WP AJAX may already be an object {success: true, data: [...]}
                let formData = [];
                try {
                    if ( typeof res === 'string' ) {
                        formData = JSON.parse( res );
                    } else if ( res && res.data ) {
                        formData = res.data;
                    } else {
                        formData = res;
                    }
                } catch (e) {
                    console.error('Failed to parse forms response', e, res);
                    return;
                }

                if (!Array.isArray(formData)) return;

                formData.forEach( data => {
                    let formId = data.id;
                    if( Object.keys(data.fields) ){
                        Object.keys(data.fields).forEach( keyObject=>{
                            let CookieData = getCookie( keyObject );
                            if( CookieData && CookieData!==null && CookieData.trim()!=='' ){
                                let InputHtml = document.getElementById('input_'+formId+'_'+data.fields[keyObject]);
                                if( InputHtml ) {
                                    InputHtml.setAttribute('value', CookieData);
                                };
                            }
                        } );
                    }
                } );
            },
            error: err => {
                console.error( 'Unable to get the forms data : ', err );
            }
        })
    }

    function clearOtherParamsData() {
        const otherParams = ['ad_group', 'matchtype', 'keyword', 'campaign_content', 'campaign_term', 'campaign_id'];
        deleteOtherParamsCookies( otherParams )
        otherParams.forEach(param => {
            if (getURLParameter(param)) {
                setSecureCookie(param, getURLParameter(param), 1);
            }
        });
    }

    function redirectWithoutParams() {
        const url = new URL(window.location.href);
        url.searchParams.delete("utm_source");
        url.searchParams.delete("utm_medium");
        url.searchParams.delete("utm_campaign");
        url.searchParams.delete("source1");
        url.searchParams.delete("medium1");
        window.location.href = url.href;
    }

    function deleteStoredData() {
        localStorage.removeItem('brr_referer');
        localStorage.removeItem('brr_medium');
        localStorage.removeItem('brr_campaign');
        localStorage.removeItem('utm_campaign_set_already');

        // Remove other params if they exist in localStorage
        ['adgroup_id', 'matchtype', 'keyword', 'campaign_content', 'campaign_term', 'campaign_id'].forEach(param => {
            localStorage.removeItem(param);
        });
    }

    // Initialize referer data and other params
    setRefererData();
    clearOtherParamsData();
    getFormData();

    // Optional: Trigger redirect if required (based on your PHP version)
    if (localStorage.getItem('brr_referer') && window.location.search) {
        const url = new URL(window.location.href);
        ['utm_source', 'utm_medium', 'utm_campaign', 'source1', 'medium1'].forEach(param => url.searchParams.delete(param));
        window.history.replaceState({}, document.title, url.toString());
    }

    function setSecureCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "; expires=" + date.toUTCString();
        document.cookie = `${name}=${value}${expires}; path=/; secure; samesite=Lax`;
    }

    function deleteStoredCookies(otherUrlParams) {
        const cookies = ["brr_referer", "brr_medium", "brr_campaign", "http_g_set_already", "utm_campaign_set_already"];
        cookies.forEach(cookie => {
            document.cookie = `${cookie}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        });
    }

    function deleteOtherParamsCookies( otherUrlParams ){
        otherUrlParams.forEach(param => {
            document.cookie = `${param}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        });
    }

    function getCookie(name) {
        // Create a regular expression to match the cookie name
        let cookieArr = document.cookie.split("; ");
        for (let i = 0; i < cookieArr.length; i++) {
            let cookiePair = cookieArr[i].split("=");
            // Decode and check if the name matches
            if (cookiePair[0] === name) {
                return decodeURIComponent(cookiePair[1]); // Return the decoded value
            }
        }
        return null; // Return null if not found
    }

    // Monitor changes to cookies and call getFormData when they change
    function observeCookieChanges() {
        let lastCookies = document.cookie;

        setInterval(() => {
            let currentCookies = document.cookie;
            if (currentCookies !== lastCookies) {
                lastCookies = currentCookies;
                getFormData(); // Call the function when cookies change
            }
        }, 500); // Check every second
    }

    // Start observing cookie changes
    observeCookieChanges();
})();