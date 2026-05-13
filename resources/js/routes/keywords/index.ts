import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\KeywordController::index
 * @see app/Http/Controllers/KeywordController.php:12
 * @route '/keywords'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/keywords',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\KeywordController::index
 * @see app/Http/Controllers/KeywordController.php:12
 * @route '/keywords'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KeywordController::index
 * @see app/Http/Controllers/KeywordController.php:12
 * @route '/keywords'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\KeywordController::index
 * @see app/Http/Controllers/KeywordController.php:12
 * @route '/keywords'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\KeywordController::show
 * @see app/Http/Controllers/KeywordController.php:24
 * @route '/keywords/{keyword}'
 */
export const show = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/keywords/{keyword}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\KeywordController::show
 * @see app/Http/Controllers/KeywordController.php:24
 * @route '/keywords/{keyword}'
 */
show.url = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { keyword: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { keyword: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    keyword: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        keyword: typeof args.keyword === 'object'
                ? args.keyword.id
                : args.keyword,
                }

    return show.definition.url
            .replace('{keyword}', parsedArgs.keyword.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KeywordController::show
 * @see app/Http/Controllers/KeywordController.php:24
 * @route '/keywords/{keyword}'
 */
show.get = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\KeywordController::show
 * @see app/Http/Controllers/KeywordController.php:24
 * @route '/keywords/{keyword}'
 */
show.head = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\KeywordController::store
 * @see app/Http/Controllers/KeywordController.php:17
 * @route '/keywords'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/keywords',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\KeywordController::store
 * @see app/Http/Controllers/KeywordController.php:17
 * @route '/keywords'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KeywordController::store
 * @see app/Http/Controllers/KeywordController.php:17
 * @route '/keywords'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\KeywordController::update
 * @see app/Http/Controllers/KeywordController.php:29
 * @route '/keywords/{keyword}'
 */
export const update = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/keywords/{keyword}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\KeywordController::update
 * @see app/Http/Controllers/KeywordController.php:29
 * @route '/keywords/{keyword}'
 */
update.url = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { keyword: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { keyword: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    keyword: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        keyword: typeof args.keyword === 'object'
                ? args.keyword.id
                : args.keyword,
                }

    return update.definition.url
            .replace('{keyword}', parsedArgs.keyword.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KeywordController::update
 * @see app/Http/Controllers/KeywordController.php:29
 * @route '/keywords/{keyword}'
 */
update.put = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\KeywordController::update
 * @see app/Http/Controllers/KeywordController.php:29
 * @route '/keywords/{keyword}'
 */
update.patch = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\KeywordController::destroy
 * @see app/Http/Controllers/KeywordController.php:36
 * @route '/keywords/{keyword}'
 */
export const destroy = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/keywords/{keyword}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\KeywordController::destroy
 * @see app/Http/Controllers/KeywordController.php:36
 * @route '/keywords/{keyword}'
 */
destroy.url = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { keyword: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { keyword: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    keyword: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        keyword: typeof args.keyword === 'object'
                ? args.keyword.id
                : args.keyword,
                }

    return destroy.definition.url
            .replace('{keyword}', parsedArgs.keyword.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KeywordController::destroy
 * @see app/Http/Controllers/KeywordController.php:36
 * @route '/keywords/{keyword}'
 */
destroy.delete = (args: { keyword: number | { id: number } } | [keyword: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const keywords = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default keywords