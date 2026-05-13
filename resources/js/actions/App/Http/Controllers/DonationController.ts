import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DonationController::store
 * @see app/Http/Controllers/DonationController.php:16
 * @route '/donations'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/donations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DonationController::store
 * @see app/Http/Controllers/DonationController.php:16
 * @route '/donations'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DonationController::store
 * @see app/Http/Controllers/DonationController.php:16
 * @route '/donations'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DonationController::update
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}'
 */
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/donations/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DonationController::update
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}'
 */
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DonationController::update
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}'
 */
update.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\DonationController::updatePix
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}/pix'
 */
export const updatePix = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePix.url(args, options),
    method: 'put',
})

updatePix.definition = {
    methods: ["put"],
    url: '/donations/{id}/pix',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DonationController::updatePix
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}/pix'
 */
updatePix.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updatePix.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DonationController::updatePix
 * @see app/Http/Controllers/DonationController.php:0
 * @route '/donations/{id}/pix'
 */
updatePix.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePix.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\DonationController::status
 * @see app/Http/Controllers/DonationController.php:28
 * @route '/donations/{id}/status'
 */
export const status = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(args, options),
    method: 'get',
})

status.definition = {
    methods: ["get","head"],
    url: '/donations/{id}/status',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DonationController::status
 * @see app/Http/Controllers/DonationController.php:28
 * @route '/donations/{id}/status'
 */
status.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return status.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DonationController::status
 * @see app/Http/Controllers/DonationController.php:28
 * @route '/donations/{id}/status'
 */
status.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DonationController::status
 * @see app/Http/Controllers/DonationController.php:28
 * @route '/donations/{id}/status'
 */
status.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: status.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DonationController::webhook
 * @see app/Http/Controllers/DonationController.php:37
 * @route '/webhook/mercadopago'
 */
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/webhook/mercadopago',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DonationController::webhook
 * @see app/Http/Controllers/DonationController.php:37
 * @route '/webhook/mercadopago'
 */
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DonationController::webhook
 * @see app/Http/Controllers/DonationController.php:37
 * @route '/webhook/mercadopago'
 */
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})
const DonationController = { store, update, updatePix, status, webhook }

export default DonationController