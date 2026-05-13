import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\DocumentCategoryController::index
 * @see app/Http/Controllers/DocumentCategoryController.php:12
 * @route '/document-categories'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/document-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DocumentCategoryController::index
 * @see app/Http/Controllers/DocumentCategoryController.php:12
 * @route '/document-categories'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentCategoryController::index
 * @see app/Http/Controllers/DocumentCategoryController.php:12
 * @route '/document-categories'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DocumentCategoryController::index
 * @see app/Http/Controllers/DocumentCategoryController.php:12
 * @route '/document-categories'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DocumentCategoryController::show
 * @see app/Http/Controllers/DocumentCategoryController.php:28
 * @route '/document-categories/{document_category}'
 */
export const show = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/document-categories/{document_category}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DocumentCategoryController::show
 * @see app/Http/Controllers/DocumentCategoryController.php:28
 * @route '/document-categories/{document_category}'
 */
show.url = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { document_category: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    document_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        document_category: args.document_category,
                }

    return show.definition.url
            .replace('{document_category}', parsedArgs.document_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentCategoryController::show
 * @see app/Http/Controllers/DocumentCategoryController.php:28
 * @route '/document-categories/{document_category}'
 */
show.get = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DocumentCategoryController::show
 * @see app/Http/Controllers/DocumentCategoryController.php:28
 * @route '/document-categories/{document_category}'
 */
show.head = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DocumentCategoryController::store
 * @see app/Http/Controllers/DocumentCategoryController.php:21
 * @route '/document-categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/document-categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DocumentCategoryController::store
 * @see app/Http/Controllers/DocumentCategoryController.php:21
 * @route '/document-categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentCategoryController::store
 * @see app/Http/Controllers/DocumentCategoryController.php:21
 * @route '/document-categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DocumentCategoryController::update
 * @see app/Http/Controllers/DocumentCategoryController.php:33
 * @route '/document-categories/{document_category}'
 */
export const update = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/document-categories/{document_category}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\DocumentCategoryController::update
 * @see app/Http/Controllers/DocumentCategoryController.php:33
 * @route '/document-categories/{document_category}'
 */
update.url = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { document_category: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    document_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        document_category: args.document_category,
                }

    return update.definition.url
            .replace('{document_category}', parsedArgs.document_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentCategoryController::update
 * @see app/Http/Controllers/DocumentCategoryController.php:33
 * @route '/document-categories/{document_category}'
 */
update.put = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\DocumentCategoryController::update
 * @see app/Http/Controllers/DocumentCategoryController.php:33
 * @route '/document-categories/{document_category}'
 */
update.patch = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\DocumentCategoryController::destroy
 * @see app/Http/Controllers/DocumentCategoryController.php:40
 * @route '/document-categories/{document_category}'
 */
export const destroy = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/document-categories/{document_category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\DocumentCategoryController::destroy
 * @see app/Http/Controllers/DocumentCategoryController.php:40
 * @route '/document-categories/{document_category}'
 */
destroy.url = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { document_category: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    document_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        document_category: args.document_category,
                }

    return destroy.definition.url
            .replace('{document_category}', parsedArgs.document_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentCategoryController::destroy
 * @see app/Http/Controllers/DocumentCategoryController.php:40
 * @route '/document-categories/{document_category}'
 */
destroy.delete = (args: { document_category: string | number } | [document_category: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const documentCategories = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default documentCategories