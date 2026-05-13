import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:16
 * @route '/admins'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admins',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:16
 * @route '/admins'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:16
 * @route '/admins'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:16
 * @route '/admins'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::store
 * @see app/Http/Controllers/AdminController.php:24
 * @route '/admins'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admins',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::store
 * @see app/Http/Controllers/AdminController.php:24
 * @route '/admins'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::store
 * @see app/Http/Controllers/AdminController.php:24
 * @route '/admins'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::show
 * @see app/Http/Controllers/AdminController.php:38
 * @route '/admins/{admin}'
 */
export const show = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admins/{admin}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::show
 * @see app/Http/Controllers/AdminController.php:38
 * @route '/admins/{admin}'
 */
show.url = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { admin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { admin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    admin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        admin: typeof args.admin === 'object'
                ? args.admin.id
                : args.admin,
                }

    return show.definition.url
            .replace('{admin}', parsedArgs.admin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::show
 * @see app/Http/Controllers/AdminController.php:38
 * @route '/admins/{admin}'
 */
show.get = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminController::show
 * @see app/Http/Controllers/AdminController.php:38
 * @route '/admins/{admin}'
 */
show.head = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::update
 * @see app/Http/Controllers/AdminController.php:46
 * @route '/admins/{admin}'
 */
export const update = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admins/{admin}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\AdminController::update
 * @see app/Http/Controllers/AdminController.php:46
 * @route '/admins/{admin}'
 */
update.url = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { admin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { admin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    admin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        admin: typeof args.admin === 'object'
                ? args.admin.id
                : args.admin,
                }

    return update.definition.url
            .replace('{admin}', parsedArgs.admin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::update
 * @see app/Http/Controllers/AdminController.php:46
 * @route '/admins/{admin}'
 */
update.put = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\AdminController::update
 * @see app/Http/Controllers/AdminController.php:46
 * @route '/admins/{admin}'
 */
update.patch = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\AdminController::destroy
 * @see app/Http/Controllers/AdminController.php:64
 * @route '/admins/{admin}'
 */
export const destroy = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admins/{admin}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\AdminController::destroy
 * @see app/Http/Controllers/AdminController.php:64
 * @route '/admins/{admin}'
 */
destroy.url = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { admin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { admin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    admin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        admin: typeof args.admin === 'object'
                ? args.admin.id
                : args.admin,
                }

    return destroy.definition.url
            .replace('{admin}', parsedArgs.admin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::destroy
 * @see app/Http/Controllers/AdminController.php:64
 * @route '/admins/{admin}'
 */
destroy.delete = (args: { admin: number | { id: number } } | [admin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const AdminController = { index, store, show, update, destroy }

export default AdminController